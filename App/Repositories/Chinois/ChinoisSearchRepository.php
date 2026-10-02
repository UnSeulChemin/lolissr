<?php

declare(strict_types=1);

namespace App\Repositories\Chinois;

use App\DTO\Chinois\Responses\ChinoisSearchItemData;
use App\Repositories\AbstractRepository;

use stdClass;

final class ChinoisSearchRepository extends AbstractRepository
{
    // =========================================
    // RECHERCHE
    // =========================================

    /**
     * @return list<ChinoisSearchItemData>
     */
    public function search(string $search): array
    {
        $search = trim($search);

        if ($search === '')
        {
            return [];
        }

        $like = "%{$search}%";

        return [
            ...$this->searchGrammaire($like),
            ...$this->searchVocabulaire($like),
        ];
    }

    // =========================================
    // GRAMMAIRE
    // =========================================

    /**
     * @return list<ChinoisSearchItemData>
     */
    private function searchGrammaire(string $like): array
    {
        /** @var list<stdClass> $results */
        $results = $this->fetchAll(
            "
            SELECT
                id,
                titre,
                explication,
                niveau

            FROM chinois_grammaire

            WHERE titre LIKE :search_titre
            OR structure LIKE :search_structure

            ORDER BY id DESC

            LIMIT 20
            ",
            [
                'search_titre' => $like,
                'search_structure' => $like,
            ]
        );

        return array_map($this->mapGrammarResult(...), $results);
    }

    // =========================================
    // VOCABULAIRE
    // =========================================

    /**
     * @return list<ChinoisSearchItemData>
     */
    private function searchVocabulaire(string $like): array
    {
        /** @var list<stdClass> $results */
        $results = $this->fetchAll(
            "
            SELECT
                id,
                mot,
                traduction,
                langue

            FROM chinois_vocabulaire

            WHERE mot LIKE :search_mot
            OR pinyin LIKE :search_pinyin

            ORDER BY id DESC

            LIMIT 20
            ",
            [
                'search_mot' => $like,
                'search_pinyin' => $like,
            ]
        );

        return array_map($this->mapVocabularyResult(...), $results);
    }

    // =========================================
    // HYDRATATION
    // =========================================

    private function mapGrammarResult(stdClass $grammaire): ChinoisSearchItemData
    {
        return new ChinoisSearchItemData(
            id: (int) $grammaire->id,
            type: 'grammaire',
            titre: (string) $grammaire->titre,
            description: mb_substr(
                strip_tags((string) ($grammaire->explication ?? '')),
                0,
                100
            ),
            niveau: (string) $grammaire->niveau
        );
    }

    private function mapVocabularyResult(stdClass $vocabulaire): ChinoisSearchItemData
    {
        return new ChinoisSearchItemData(
            id: (int) $vocabulaire->id,
            type: 'vocabulaire',
            titre: (string) $vocabulaire->mot,
            description: (string) $vocabulaire->traduction,
            langue: (string) $vocabulaire->langue
        );
    }
}
