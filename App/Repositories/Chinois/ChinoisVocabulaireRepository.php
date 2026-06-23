<?php

declare(strict_types=1);

namespace App\Repositories\Chinois;

use App\DTO\Chinois\Responses\ChinoisVocabulaireData;
use App\Models\Model;

use stdClass;

final class ChinoisVocabulaireRepository extends Model
{
    protected string $table = 'chinois_vocabulaire';

    private const SELECT_FIELDS = '
        id,
        langue,
        mot,
        pinyin,
        type,
        traduction,
        exemple,
        maitrise,
        xp_rewarded
    ';

    /**
     * @return list<ChinoisVocabulaireData>
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
     * @return list<ChinoisVocabulaireData>
     */
    public function findByLangue(string $langue): array
    {
        $query = $this->query(
            "SELECT " . self::SELECT_FIELDS . "

            FROM {$this->table()}

            WHERE langue = ?

            ORDER BY id DESC",
            [$langue]
        );

        if ($query === false)
        {
            return [];
        }

        /** @var list<stdClass> $results */
        $results = $query->fetchAll();

        return $this->mapResultsToDto($results);
    }

    public function findById(int $id): ?ChinoisVocabulaireData
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

    public function deleteVocabulaire(int $id): bool
    {
        return $this->delete(['id' => $id]);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function updateVocabulaire(int $id, array $data): bool
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

    private function mapRowToDto(stdClass $row): ChinoisVocabulaireData
    {
        return new ChinoisVocabulaireData(
            id: (int) $row->id,
            langue: (string) $row->langue,
            mot: (string) $row->mot,
            pinyin: (string) $row->pinyin,
            type: (string) $row->type,
            traduction: (string) $row->traduction,
            exemple: $row->exemple !== null ? (string) $row->exemple : null,
            maitrise: (bool) $row->maitrise,
            xpRewarded: (bool) $row->xp_rewarded
        );
    }

    /**
     * @param list<stdClass> $results
     * @return list<ChinoisVocabulaireData>
     */
    private function mapResultsToDto(array $results): array
    {
        return array_map($this->mapRowToDto(...), $results);
    }
}
