<?php

declare(strict_types=1);

namespace App\Repositories\Chinois;

use App\DTO\Chinois\Responses\ChinoisGrammaireData;
use App\Models\Model;

use stdClass;

final class ChinoisGrammaireRepository extends Model
{
    protected string $table = 'chinois_grammaire';

    private const SELECT_FIELDS = '
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
    ';

    /**
     * @return list<ChinoisGrammaireData>
     */
    public function findNotMasteredDto(): array
    {
        $query = $this->query("SELECT " . self::SELECT_FIELDS . " FROM {$this->table()} WHERE maitrise = 0 ORDER BY id ASC");

        if ($query === false)
        {
            return [];
        }

        /** @var list<stdClass> $results */
        $results = $query->fetchAll();

        return $this->mapResultsToDto($results);
    }

    /**
     * @return list<ChinoisGrammaireData>
     */
    public function findByLevel(string $niveau): array
    {
        $query = $this->query(
            "SELECT " . self::SELECT_FIELDS . "

            FROM {$this->table()}

            WHERE niveau = ?

            ORDER BY
                section_position ASC,
                categorie_position ASC,
                position ASC,
                id ASC",
            [$niveau]
        );

        if ($query === false)
        {
            return [];
        }

        /** @var list<stdClass> $results */
        $results = $query->fetchAll();

        return $this->mapResultsToDto($results);
    }

    public function findById(int $id): ?ChinoisGrammaireData
    {
        $result = $this->fetchOne("SELECT " . self::SELECT_FIELDS . " FROM {$this->table()} WHERE id = ? LIMIT 1", [$id]);

        if ($result === null)
        {
            return null;
        }

        /** @var stdClass $result */
        return $this->mapRowToDto($result);
    }

    public function toggleMaitrise(int $id): bool
    {
        $this->execute("UPDATE {$this->table()} SET maitrise = NOT maitrise WHERE id = ?", [$id]);

        $result = $this->fetchOne("SELECT maitrise FROM {$this->table()} WHERE id = ?", [$id]);

        if ($result === null)
        {
            return false;
        }

        /** @var array{maitrise?: mixed} $data */
        $data = (array) $result;

        return (bool) ($data['maitrise'] ?? false);
    }

    public function deleteGrammaire(int $id): bool
    {
        return $this->delete(['id' => $id]);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function updateGrammaire(int $id, array $data): bool
    {
        return $this->update($data, ['id' => $id]);
    }

    public function countAll(): int
    {
        return $this->countRows();
    }

    public function countRemaining(): int
    {
        return $this->countWhere('maitrise = 0');
    }

    public function countMastered(): int
    {
        return $this->countWhere('maitrise = 1');
    }

    public function markXpRewarded(int $id): bool
    {
        return $this->update(['xp_rewarded' => 1], ['id' => $id]);
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    private function mapRowToDto(stdClass $row): ChinoisGrammaireData
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

            xpRewarded: (bool) $row->xp_rewarded
        );
    }

    /**
     * @param list<stdClass> $results
     * @return list<ChinoisGrammaireData>
     */
    private function mapResultsToDto(array $results): array
    {
        return array_map($this->mapRowToDto(...), $results);
    }
}
