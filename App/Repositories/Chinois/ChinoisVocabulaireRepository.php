<?php

declare(strict_types=1);

namespace App\Repositories\Chinois;

use App\DTO\Chinois\Responses\ChinoisVocabulaireData;
use App\Models\ChinoisVocabulaire;
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

        return (bool) $result->maitrise;
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
        $result =
            $this->fetchOne(
                "SELECT COUNT(*) AS total
                FROM {$this->table()}"
            );

        return $result !== null
            ? (int) $result->total
            : 0;
    }

    public function countRemaining(): int
    {
        $result =
            $this->fetchOne(
                "SELECT COUNT(*) AS total
                FROM {$this->table()}
                WHERE maitrise = 0"
            );

        return $result !== null
            ? (int) $result->total
            : 0;
    }

    /**
     * @return list<ChinoisVocabulaire>
     */
    public function findNotMastered(): array
    {
        /** @var list<ChinoisVocabulaire> $vocabulaires */
        $vocabulaires =
            $this->fetchAll(
                "SELECT
                    id,
                    langue,
                    mot,
                    pinyin,
                    type,
                    traduction,
                    exemple,
                    maitrise,
                    xp_rewarded,
                    created_at

                FROM {$this->table()}

                WHERE maitrise = 0

                ORDER BY id ASC",
                [],
                ChinoisVocabulaire::class,
            );

        return $vocabulaires;
    }

    public function countMastered(): int
    {
        $result =
            $this->fetchOne(
                "SELECT COUNT(*) AS total
                FROM {$this->table()}
                WHERE maitrise = 1"
            );

        return $result !== null
            ? (int) $result->total
            : 0;
    }

    public function markXpRewarded(
        int $id,
    ): bool {

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