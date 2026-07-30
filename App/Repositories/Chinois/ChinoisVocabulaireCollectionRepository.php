<?php

declare(strict_types=1);

namespace App\Repositories\Chinois;

use App\DTO\Chinois\Responses\ChinoisVocabulaireData;
use App\Models\Model;
use App\Repositories\Chinois\Concerns\MapsChinoisVocabulaire;

use stdClass;

final class ChinoisVocabulaireCollectionRepository extends Model
{
    use MapsChinoisVocabulaire;

    protected string $table = 'chinois_vocabulaire';

    // =========================================
    // COLLECTION
    // =========================================

    public function countByLangue(string $langue): int
    {
        /** @var stdClass|null $result */
        $result = $this->fetchOne(
            "
            SELECT COUNT(*) AS total

            FROM {$this->table()}

            WHERE langue = :langue
            ",
            [
                'langue' => trim($langue),
            ]
        );

        return (int) ($result?->total ?? 0);
    }

    /**
     * @return list<ChinoisVocabulaireData>
     */
    public function findByLanguePaginated(string $langue, int $perPage, int $page): array
    {
        $page = max(1, $page);
        $perPage = max(1, $perPage);
        $offset = ($page - 1) * $perPage;

        /** @var list<stdClass> $results */
        $results = $this->fetchAll(
            "
            SELECT
                " . self::SELECT_FIELDS . "

            FROM {$this->table()}

            WHERE langue = :langue

            ORDER BY maitrise ASC, id DESC

            LIMIT {$perPage}
            OFFSET {$offset}
            ",
            [
                'langue' => trim($langue),
            ]
        );

        return array_map($this->mapRowToDto(...), $results);
    }
}