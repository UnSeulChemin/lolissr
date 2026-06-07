<?php

declare(strict_types=1);

namespace App\Repositories\Chinois;

use App\Models\ChinoisVocabulaire;
use App\Models\Model;
use Framework\Application\App;
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

    /**
     * @return list<ChinoisVocabulaire>
     */
    public function findByLangue(
        string $langue,
    ): array {

        /** @var list<ChinoisVocabulaire> $vocabulaire */
        $vocabulaire =
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
                    created_at

                FROM {$this->getTable()}

                WHERE langue = ?

                ORDER BY id DESC",
                [$langue],
                ChinoisVocabulaire::class,
            );

        return $vocabulaire;
    }

    public function findById(
        int $id,
    ): ?ChinoisVocabulaire {

        /** @var ChinoisVocabulaire|null $vocabulaire */
        $vocabulaire =
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
                    created_at

                FROM {$this->getTable()}

                WHERE id = ?

                LIMIT 1",
                [$id],
                ChinoisVocabulaire::class,
            );

        return $vocabulaire;
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
                FROM {$this->getTable()}"
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
                FROM {$this->getTable()}
                WHERE maitrise = 0"
            );

        return $result !== null
            ? (int) $result->total
            : 0;
    }
}