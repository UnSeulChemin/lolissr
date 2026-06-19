<?php

declare(strict_types=1);

namespace App\Repositories\Chinois;

use App\DTO\Chinois\Responses\ChinoisVocabulaireData;
use App\Models\Model;

use Framework\Application\App;

use stdClass;
use LogicException;

final class ChinoisVocabulaireRepository extends Model
{
    protected string $table =
        'chinois_vocabulaire';

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
    ): ChinoisVocabulaireData {
        return new ChinoisVocabulaireData(
            id: (int) $row->id,
            langue: (string) $row->langue,
            mot: (string) $row->mot,
            pinyin: (string) $row->pinyin,
            type: (string) $row->type,
            traduction: (string) $row->traduction,

            exemple:
                $row->exemple !== null
                    ? (string) $row->exemple
                    : null,

            maitrise:
                (bool) $row->maitrise,

            xpRewarded:
                (bool) $row->xp_rewarded,
        );
    }

    /**
     * @return list<ChinoisVocabulaireData>
     */
    public function findNotMasteredDto(): array
    {
        $query = $this->query(
            "SELECT
                id,
                langue,
                mot,
                pinyin,
                type,
                traduction,
                exemple,
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
     * @return list<ChinoisVocabulaireData>
     */
    public function findByLangue(
        string $langue,
    ): array
    {
        $query = $this->query(
            "SELECT
                id,
                langue,
                mot,
                pinyin,
                type,
                traduction,
                exemple,
                maitrise,
                xp_rewarded

            FROM {$this->table()}

            WHERE langue = ?

            ORDER BY id DESC",
            [$langue],
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

    public function findById(
        int $id,
    ): ?ChinoisVocabulaireData {

        $result =
            $this->fetchOne(
                "SELECT
                    id,
                    langue,
                    mot,
                    pinyin,
                    type,
                    traduction,
                    exemple,
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

    public function deleteVocabulaire(
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
    public function updateVocabulaire(
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
        return $this->countRows();
    }

    public function countRemaining(): int
    {
        return $this->countWhere(
            'maitrise = 0',
        );
    }

    public function countMastered(): int
    {
        return $this->countWhere(
            'maitrise = 1',
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