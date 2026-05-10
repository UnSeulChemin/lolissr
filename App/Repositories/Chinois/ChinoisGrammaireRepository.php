<?php

declare(strict_types=1);

namespace App\Repositories\Chinois;

use App\DTO\Chinois\ChinoisGrammaireDTO;
use App\Models\Model;

final class ChinoisGrammaireRepository extends Model
{
    protected string $table = 'chinois_grammaires';

    /**
     * @return ChinoisGrammaireDTO[]
     */
    public function findByLevel(string $niveau): array
    {
        $query = $this->requete(
            "
            SELECT
                id,
                niveau,
                titre,
                structure_grammaire,
                phrase_chinoise,
                pinyin,
                traduction,
                explication,
                ordre_affichage
            FROM {$this->getTable()}
            WHERE niveau = ?
            ORDER BY ordre_affichage ASC
            ",
            [$niveau]
        );

        if (!$query)
        {
            return [];
        }

        $results = $query->fetchAll();

        return array_map(
            static fn(object $row): ChinoisGrammaireDTO => new ChinoisGrammaireDTO(
                id: (int) $row->id,
                niveau: (string) $row->niveau,
                titre: (string) $row->titre,
                structureGrammaire: (string) $row->structure_grammaire,
                phraseChinoise: (string) $row->phrase_chinoise,
                pinyin: (string) $row->pinyin,
                traduction: (string) $row->traduction,
                explication: (string) $row->explication,
                ordreAffichage: (int) $row->ordre_affichage,
            ),
            $results
        );
    }
}