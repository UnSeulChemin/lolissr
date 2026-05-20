<?php

declare(strict_types=1);

namespace App\Repositories\Chinois;

use App\DTO\Chinois\ChinoisGrammaireDTO;
use App\Models\Model;

final class ChinoisGrammaireRepository extends Model
{
    protected string $table = 'chinois_grammaire';

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
                section,
                section_position,
                categorie,
                categorie_position,
                titre,
                structure,
                phrase,
                pinyin,
                traduction,
                explication,
                position,
                maitrise
            FROM {$this->getTable()}
            WHERE niveau = ?
            ORDER BY
                section_position ASC,
                categorie_position ASC,
                position ASC,
                id ASC
            ",
            [$niveau]
        );

        if (!$query) {
            return [];
        }

        $results = $query->fetchAll();

        return array_map(
            static fn (object $row): ChinoisGrammaireDTO => new ChinoisGrammaireDTO(
                id: (int) $row->id,
                niveau: (string) $row->niveau,
                section: (string) $row->section,
                sectionPosition: (int) $row->section_position,
                categorie: (string) $row->categorie,
                categoriePosition: (int) $row->categorie_position,
                titre: (string) $row->titre,
                structure: (string) $row->structure,
                phrase: (string) $row->phrase,
                pinyin: (string) $row->pinyin,
                traduction: (string) $row->traduction,
                explication: (string) $row->explication,
                position: (int) $row->position,
                maitrise: (bool) $row->maitrise,
            ),
            $results
        );
    }

    public function toggleMaitrise(int $id): int
    {
        $this->requete(
            '
            UPDATE chinois_grammaire
            SET maitrise = NOT maitrise
            WHERE id = ?
            ',
            [$id]
        );

        $query = $this->requete(
            "
            SELECT maitrise
            FROM {$this->getTable()}
            WHERE id = ?
            ",
            [$id]
        );

        return (int) $query->fetch()->maitrise;
    }
}
