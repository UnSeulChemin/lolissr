<?php

declare(strict_types=1);

namespace App\Repositories\Manga;

use App\Models\Model;
use stdClass;

final class MangaStatsRepository extends Model
{
    protected string $table = 'manga';

    /**
     * @param array<string, mixed> $params
     */
    private function fetchSingleValue(
        string $sql,
        string $field,
        array $params = [],
        mixed $default = 0
    ): mixed {
        $result = $this->fetchOne(
            $sql,
            $params
        );

        if (!$result instanceof stdClass) {
            return $default;
        }

        return property_exists($result, $field)
            ? $result->{$field}
            : $default;
    }

    public function countAllTomes(): int
    {
        return (int) $this->fetchSingleValue(
            "SELECT COUNT(*) AS total
            FROM {$this->getTable()}",
            'total'
        );
    }

    public function countSeries(): int
    {
        return (int) $this->fetchSingleValue(
            "SELECT COUNT(DISTINCT livre) AS total
            FROM {$this->getTable()}",
            'total'
        );
    }

    public function countRead(): int
    {
        return (int) $this->fetchSingleValue(
            "SELECT COUNT(*) AS total
            FROM {$this->getTable()}
            WHERE lu = 1",
            'total'
        );
    }

    public function averageNote(): ?float
    {
        $average = $this->fetchSingleValue(
            "SELECT AVG(COALESCE(note, 0)) AS moyenne
            FROM {$this->getTable()}",
            'moyenne',
            [],
            null
        );

        return $average !== null
            ? (float) $average
            : null;
    }

    public function findLastAdded(): ?object
    {
        return $this->fetchOne(
            "SELECT *
            FROM {$this->getTable()}
            ORDER BY id DESC
            LIMIT 1"
        );
    }

    public function findLongestSeries(): ?object
    {
        return $this->fetchOne(
            "SELECT m1.slug,
                    m1.livre,
                    m1.thumbnail,
                    m1.extension,
                    counts.total
            FROM {$this->getTable()} m1
            INNER JOIN (
                SELECT slug,
                       COUNT(*) AS total
                FROM {$this->getTable()}
                GROUP BY slug
                ORDER BY total DESC
                LIMIT 1
            ) counts ON counts.slug = m1.slug
            WHERE m1.numero = 1
            LIMIT 1"
        );
    }

    /**
     * @return array<int, object>
     */
    public function topLongestSeries(
        int $limit = 5
    ): array {
        $limit = max(1, $limit);

        return $this->fetchAll(
            "SELECT m1.slug,
                    m1.livre,
                    m1.thumbnail,
                    m1.extension,
                    counts.total
            FROM {$this->getTable()} m1
            INNER JOIN (
                SELECT slug,
                       COUNT(*) AS total
                FROM {$this->getTable()}
                GROUP BY slug
                ORDER BY total DESC
                LIMIT {$limit}
            ) counts ON counts.slug = m1.slug
            WHERE m1.numero = 1
            ORDER BY counts.total DESC,
                     m1.livre ASC"
        );
    }
}
