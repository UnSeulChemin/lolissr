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

    private function mapRowToDto(
        stdClass $row,
    ): ChinoisGrammaireData
    {
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
                $row->pinyin !== null
                    ? (string) $row->pinyin
                    : '',

            traduction: (string) $row->traduction,

            explication:
                $row->explication !== null
                    ? (string) $row->explication
                    : null,

            position: (int) $row->position,
            maitrise: (bool) $row->maitrise,

            xpRewarded:
                (bool) $row->xp_rewarded,
        );
    }

    /**
     * @return list<ChinoisGrammaireData>
     */
    public function findNotMasteredDto(): array
    {
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
                maitrise,
                xp_rewarded

            FROM {$this->table()}

            WHERE maitrise = 0

            ORDER BY id ASC"
        );

        if ($query === false)
        {
            return [];
        }

        /** @var list<stdClass> $results */
        $results = $query->fetchAll();

        return array_map(
            fn (stdClass $row)
                => $this->mapRowToDto($row),
            $results,
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
                maitrise,
                xp_rewarded

            FROM {$this->table()}

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
            fn (stdClass $row)
                => $this->mapRowToDto($row),
            $results,
        );
    }

    public function toggleMaitrise(
        int $id,
    ): bool {

        $this->guardWrite();

        $this->execute(
            "UPDATE {$this->table()}
            SET maitrise = NOT maitrise
            WHERE id = ?",
            [$id],
        );

        $result =
            $this->fetchOne(
                "SELECT maitrise
                FROM {$this->table()}
                WHERE id = ?",
                [$id],
            );

        if ($result === null)
        {
            return false;
        }

        /** @var array{maitrise?: mixed} $data */
        $data = (array) $result;

        return (bool) (
            $data['maitrise']
            ?? false
        );
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
                    maitrise,
                    xp_rewarded

                FROM {$this->table()}

                WHERE id = ?

                LIMIT 1",
                [$id],
            );

        if ($result === null)
        {
            return null;
        }

        /** @var stdClass $result */
        return $this->mapRowToDto($result);
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

    /**
     * @param array<string, mixed> $data
     */
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

    public function countAll(): int
    {
        $result =
            $this->fetchOne(
                "SELECT COUNT(*) AS total
                FROM {$this->table()}"
            );

        if ($result === null)
        {
            return 0;
        }

        /** @var array{total?: mixed} $data */
        $data = (array) $result;

        return (int) (
            $data['total']
            ?? 0
        );
    }

    public function countRemaining(): int
    {
        $result =
            $this->fetchOne(
                "SELECT COUNT(*) AS total
                FROM {$this->table()}
                WHERE maitrise = 0"
            );

        if ($result === null)
        {
            return 0;
        }

        /** @var array{total?: mixed} $data */
        $data = (array) $result;

        return (int) (
            $data['total']
            ?? 0
        );
    }

    public function countMastered(): int
    {
        $result =
            $this->fetchOne(
                "SELECT COUNT(*) AS total
                FROM {$this->table()}
                WHERE maitrise = 1"
            );

        if ($result === null)
        {
            return 0;
        }

        /** @var array{total?: mixed} $data */
        $data = (array) $result;

        return (int) (
            $data['total']
            ?? 0
        );
    }

    public function markXpRewarded(
        int $id,
    ): bool {

        $this->guardWrite();

        return $this->update(
            [
                'xp_rewarded' => 1,
            ],
            [
                'id' => $id,
            ],
        );
    }
}