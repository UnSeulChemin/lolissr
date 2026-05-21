<?php

declare(strict_types=1);

namespace App\Repositories\Chinois;

use App\DTO\Chinois\ChinoisGrammaireDTO;
use App\Models\Model;
use stdClass;

final class ChinoisGrammaireRepository extends Model
{
    protected string $table = 'chinois_grammaire';

    /**
     * @return list<ChinoisGrammaireDTO>
     */
    public function findByLevel(
        string $niveau,
    ): array {
        $query = $this->query(
            "SELECT
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
                id ASC",
            [$niveau],
        );

        if ($query === false) {
            return [];
        }

        /** @var list<stdClass> $results */
        $results = $query->fetchAll();

        return array_map(
            static function (
                stdClass $row,
            ): ChinoisGrammaireDTO {
                return new ChinoisGrammaireDTO(
                    id: (int) $row->id,

                    niveau: (string) $row->niveau,

                    section: (string) $row->section,

                    sectionPosition: (int) $row->section_position,

                    categorie: (string) $row->categorie,

                    categoriePosition: (int) $row->categorie_position,

                    titre: (string) $row->titre,

                    structure: (string) $row->structure,

                    phrase: (string) $row->phrase,

                    pinyin: isset($row->pinyin)
                        ? (string) $row->pinyin
                        : '',

                    traduction: (string) $row->traduction,

                    explication: $row->explication !== null
                        ? (string) $row->explication
                        : null,

                    position: (int) $row->position,

                    maitrise: (bool) $row->maitrise,
                );
            },
            $results,
        );
    }

    public function toggleMaitrise(
        int $id,
    ): int {
        $this->execute(
            "UPDATE {$this->getTable()}
            SET maitrise = NOT maitrise
            WHERE id = ?",
            [$id],
        );

        $result = $this->fetchOne(
            "SELECT maitrise
            FROM {$this->getTable()}
            WHERE id = ?",
            [$id],
        );

        if ($result === null) {
            return 0;
        }

        return (int) $result->maitrise;
    }
}