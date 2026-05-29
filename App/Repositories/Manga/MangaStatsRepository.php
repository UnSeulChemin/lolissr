<?php

declare(strict_types=1);

namespace App\Repositories\Manga;

use App\Models\Manga;
use App\Models\Model;

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
        mixed $default = 0,
    ): mixed {
        $result = $this->fetchOne(
            $sql,
            $params,
        );

        if ($result === null)
        {
            return $default;
        }

        $resultArray = (array) $result;

        if (!array_key_exists($field, $resultArray))
        {
            return $default;
        }

        return $resultArray[$field];
    }

    public function countAllTomes(): int
    {
        return (int) $this->fetchSingleValue(
            "SELECT COUNT(*) AS total
            FROM {$this->getTable()}",
            'total',
        );
    }

    public function countSeries(): int
    {
        return (int) $this->fetchSingleValue(
            "SELECT COUNT(DISTINCT slug) AS total
            FROM {$this->getTable()}",
            'total',
        );
    }

    public function countRead(): int
    {
        return (int) $this->fetchSingleValue(
            "SELECT COUNT(*) AS total
            FROM {$this->getTable()}
            WHERE lu = 1",
            'total',
        );
    }

    public function averageNote(): ?float
    {
        $average = $this->fetchSingleValue(
            "SELECT ROUND(
                AVG(note),
                1
            ) AS moyenne
            FROM {$this->getTable()}
            WHERE note IS NOT NULL",
            'moyenne',
            [],
            null,
        );

        return $average !== null
            ? (float) $average
            : null;
    }

    public function findLastAdded(): ?Manga
    {
        /** @var Manga|null $manga */
        $manga = $this->fetchOne(
            "SELECT *
            FROM {$this->getTable()}
            ORDER BY id DESC
            LIMIT 1",
            [],
            Manga::class,
        );

        return $manga;
    }

    public function findLongestSeries(): ?Manga
    {
        /** @var Manga|null $manga */
        $manga = $this->fetchOne(
            "SELECT
                m.*,
                stats.total

            FROM {$this->getTable()} m

            INNER JOIN (
                SELECT
                    slug,
                    COUNT(*) AS total

                FROM {$this->getTable()}

                GROUP BY slug

                ORDER BY total DESC

                LIMIT 1

            ) stats
                ON stats.slug = m.slug

            WHERE m.numero = 1

            LIMIT 1",
            [],
            Manga::class,
        );

        return $manga;
    }

    /**
     * @return list<Manga>
     */
    public function topLongestSeries(
        int $limit = 5,
    ): array {
        $limit = max(
            1,
            $limit,
        );

        /** @var list<Manga> $mangas */
        $mangas = $this->fetchAll(
            "SELECT
                m.*,
                stats.total

            FROM {$this->getTable()} m

            INNER JOIN (
                SELECT
                    slug,
                    COUNT(*) AS total

                FROM {$this->getTable()}

                GROUP BY slug

                ORDER BY total DESC

                LIMIT {$limit}

            ) stats
                ON stats.slug = m.slug

            WHERE m.numero = 1

            ORDER BY
                stats.total DESC,
                m.livre ASC",
            [],
            Manga::class,
        );

        return $mangas;
    }
}
