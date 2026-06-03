<?php

declare(strict_types=1);

namespace App\Repositories\Chinois;

use App\DTO\Chinois\Responses\ChinoisGrammaireData;
use App\Models\Model;
use Framework\Application\App;
use LogicException;
use stdClass;

final class ChinoisGrammaireRepository extends Model
{
    protected string $table =
        'chinois_grammaire';

    private function guardWrite(): void
    {
        if (! App::isReadOnly())
        {
            return;
        }

        throw new LogicException(
            'Écriture en base interdite en mode lecture seule.',
        );
    }

    /**
     * @return list<ChinoisGrammaireData>
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
                abreviation,
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

        if ($query === false)
        {
            return [];
        }

        /** @var list<stdClass> $results */
        $results =
            $query->fetchAll();

        return array_map(
            static function (
                stdClass $row,
            ): ChinoisGrammaireData {

                return new ChinoisGrammaireData(
                    id: (int) $row->id,
                    niveau: (string) $row->niveau,

                    section: (string) $row->section,
                    sectionPosition: (int) $row->section_position,

                    categorie: (string) $row->categorie,
                    categoriePosition: (int) $row->categorie_position,

                    titre: (string) $row->titre,
                    structure: (string) $row->structure,

                    abreviation:
                        $row->abreviation !== null
                            ? (string) $row->abreviation
                            : null,

                    phrase: (string) $row->phrase,

                    pinyin:
                        isset($row->pinyin)
                            ? (string) $row->pinyin
                            : '',

                    traduction: (string) $row->traduction,

                    explication:
                        $row->explication !== null
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

        $this->guardWrite();

        $this->execute(
            "UPDATE {$this->getTable()}
            SET maitrise = NOT maitrise
            WHERE id = ?",
            [$id],
        );

        $result =
            $this->fetchOne(
                "SELECT maitrise
                FROM {$this->getTable()}
                WHERE id = ?",
                [$id],
            );

        if ($result === null)
        {
            return 0;
        }

        return (int) $result->maitrise;
    }

    public function findById(
        int $id,
    ): ?ChinoisGrammaireData {

        $result =
            $this->fetchOne(
                "SELECT
                    id,
                    niveau,
                    section,
                    section_position,
                    categorie,
                    categorie_position,
                    titre,
                    structure,
                    abreviation,
                    phrase,
                    pinyin,
                    traduction,
                    explication,
                    position,
                    maitrise

                FROM {$this->getTable()}

                WHERE id = ?

                LIMIT 1",
                [$id],
            );

        if ($result === null)
        {
            return null;
        }

        return new ChinoisGrammaireData(
            id: (int) $result->id,
            niveau: (string) $result->niveau,

            section: (string) $result->section,
            sectionPosition: (int) $result->section_position,

            categorie: (string) $result->categorie,
            categoriePosition: (int) $result->categorie_position,

            titre: (string) $result->titre,
            structure: (string) $result->structure,

            abreviation:
                $result->abreviation !== null
                    ? (string) $result->abreviation
                    : null,

            phrase: (string) $result->phrase,

            pinyin:
                isset($result->pinyin)
                    ? (string) $result->pinyin
                    : '',

            traduction: (string) $result->traduction,

            explication:
                $result->explication !== null
                    ? (string) $result->explication
                    : null,

            position: (int) $result->position,
            maitrise: (bool) $result->maitrise,
        );
    }

    public function deleteGrammaire(
        int $id,
    ): bool {

        $this->guardWrite();

        return $this->delete(
            [
                'id' => $id,
            ],
        );
    }

    public function updateGrammaire(
        int $id,
        array $data,
    ): bool {

        $this->guardWrite();

        return $this->update(
            $data,
            [
                'id' => $id,
            ],
        );
    }
}