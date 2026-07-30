<?php

declare(strict_types=1);

namespace App\Repositories\Chinois;

use App\DTO\Chinois\Responses\ChinoisVocabulaireData;
use App\Models\Model;
use App\Repositories\Chinois\Concerns\MapsChinoisVocabulaire;

use stdClass;

final class ChinoisVocabulaireRepository extends Model
{
    use MapsChinoisVocabulaire;

    protected string $table = 'chinois_vocabulaire';

    // =========================================
    // LECTURE
    // =========================================

    /**
     * @return list<ChinoisVocabulaireData>
     */
    public function findNotMasteredDto(): array
    {
        /** @var list<stdClass> $results */
        $results = $this->fetchAll(
            "
            SELECT
                " . self::SELECT_FIELDS . "

            FROM {$this->table()}

            WHERE maitrise = 0

            ORDER BY id ASC
            "
        );

        return array_map($this->mapRowToDto(...), $results);
    }

    public function findById(int $id): ?ChinoisVocabulaireData
    {
        return $this->findOneBy([
            'id' => $id,
        ]);
    }

    public function findByLangueAndId(string $langue, int $id): ?ChinoisVocabulaireData
    {
        return $this->findOneBy([
            'id' => $id,
            'langue' => trim($langue),
        ]);
    }

    // =========================================
    // ÉCRITURE
    // =========================================

    public function updateVocabulaire(
        int $id,
        string $langue,
        string $mot,
        string $pinyin,
        string $type,
        string $traduction,
        string $exemple
    ): bool {
        return $this->updateById($id, [
            'langue' => trim($langue),
            'mot' => trim($mot),
            'pinyin' => trim($pinyin),
            'type' => trim($type),
            'traduction' => trim($traduction),
            'exemple' => trim($exemple),
        ]);
    }

    public function deleteVocabulaire(int $id): bool
    {
        return $this->delete([
            'id' => $id,
        ]);
    }

    // =========================================
    // MAÎTRISE
    // =========================================

    public function toggleMaitrise(int $id): ?bool
    {
        $statement = $this->query(
            "
            UPDATE {$this->table()}

            SET maitrise = NOT maitrise

            WHERE id = :id
            ",
            [
                'id' => $id,
            ]
        );

        if ($statement === false || $statement->rowCount() !== 1)
        {
            return null;
        }

        /** @var stdClass|null $result */
        $result = $this->fetchOne(
            "
            SELECT maitrise

            FROM {$this->table()}

            WHERE id = :id

            LIMIT 1
            ",
            [
                'id' => $id,
            ]
        );

        return $result !== null ? (bool) $result->maitrise : null;
    }

    // =========================================
    // XP
    // =========================================

    public function claimXpReward(int $id): bool
    {
        $statement = $this->query(
            "
            UPDATE {$this->table()}

            SET xp_rewarded = 1

            WHERE id = :id
            AND xp_rewarded = 0
            ",
            [
                'id' => $id,
            ]
        );

        return $statement !== false && $statement->rowCount() === 1;
    }

    // =========================================
    // HYDRATATION
    // =========================================

    /**
     * @param array<string, int|string> $criteria
     */
    private function findOneBy(array $criteria): ?ChinoisVocabulaireData
    {
        $conditions = [];
        $params = [];

        foreach ($criteria as $column => $value)
        {
            $conditions[] = "{$column} = :{$column}";
            $params[$column] = $value;
        }

        /** @var stdClass|null $result */
        $result = $this->fetchOne(
            "
            SELECT
                " . self::SELECT_FIELDS . "

            FROM {$this->table()}

            WHERE " . implode("\nAND ", $conditions) . "

            LIMIT 1
            ",
            $params
        );

        return $result !== null ? $this->mapRowToDto($result) : null;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function updateById(int $id, array $data): bool
    {
        return $this->update(
            $data,
            [
                'id' => $id,
            ]
        );
    }
}