<?php

declare(strict_types=1);

namespace App\Repositories\Chinois;

use App\DTO\Chinois\Responses\ChinoisSearchItemData;
use App\Models\Model;

use stdClass;

final class ChinoisSearchRepository extends Model
{
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

        $results = [];

        /*
        |------------------------------------------------------------------
        | GRAMMAIRE
        |------------------------------------------------------------------
        */

        $grammaireQuery = $this->query(
            "
            SELECT
                id,
                titre,
                explication,
                niveau

            FROM chinois_grammaire

            WHERE
                titre LIKE ?
                OR structure LIKE ?

            ORDER BY id DESC

            LIMIT 20
            ",
            [$like, $like]
        );

        if ($grammaireQuery !== false)
        {
            /** @var list<stdClass> $grammaires */
            $grammaires = $grammaireQuery->fetchAll();

            $results = [...$results, ...array_map($this->mapGrammarResult(...), $grammaires)];
        }

        /*
        |------------------------------------------------------------------
        | VOCABULAIRE
        |------------------------------------------------------------------
        */

        $vocabulaireQuery = $this->query(
            "
            SELECT
                id,
                mot,
                traduction,
                langue

            FROM chinois_vocabulaire

            WHERE
                mot LIKE ?
                OR pinyin LIKE ?

            ORDER BY id DESC

            LIMIT 20
            ",
            [$like, $like]
        );

        if ($vocabulaireQuery !== false)
        {
            /** @var list<stdClass> $vocabulaires */
            $vocabulaires = $vocabulaireQuery->fetchAll();

            $results = [...$results, ...array_map($this->mapVocabularyResult(...), $vocabulaires)];
        }

        return $results;
    }

    /*
    |------------------------------------------------------------------
    | HELPERS
    |------------------------------------------------------------------
    */

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
            niveau: (string) ($grammaire->niveau ?? '')
        );
    }

    private function mapVocabularyResult(stdClass $vocabulaire): ChinoisSearchItemData
    {
        return new ChinoisSearchItemData(
            id: (int) $vocabulaire->id,
            type: 'vocabulaire',
            titre: (string) $vocabulaire->mot,
            description: (string) $vocabulaire->traduction,
            langue: (string) ($vocabulaire->langue ?? '')
        );
    }
}
