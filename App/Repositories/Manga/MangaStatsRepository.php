<?php

declare(strict_types=1);

namespace App\Repositories\Manga;

use App\DTO\Manga\Responses\MangaStatsData;
use App\Models\Manga;
use App\Models\Model;

use Framework\Support\Str;

final class MangaStatsRepository extends Model
{
    protected string $table = 'manga';

    private function mapToStatsDto(
        Manga $manga,
    ): MangaStatsData {
        return new MangaStatsData(
            id: $manga->id,
            slug: $manga->slug,
            livre: $manga->livre,

            thumbnail:
                $manga->thumbnail !== ''
                    ? $manga->thumbnail
                    : null,

            extension:
                $manga->extension !== ''
                    ? $manga->extension
                    : null,

            numero: $manga->numero,

            total: $manga->total,
        );
    }

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
            FROM {$this->table()}",
            'total',
        );
    }

    public function countSeries(): int
    {
        return (int) $this->fetchSingleValue(
            "SELECT COUNT(DISTINCT slug) AS total
            FROM {$this->table()}",
            'total',
        );
    }

    public function countRead(): int
    {
        return (int) $this->fetchSingleValue(
            "SELECT COUNT(*) AS total
            FROM {$this->table()}
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
            FROM {$this->table()}
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
            FROM {$this->table()}
            ORDER BY id DESC
            LIMIT 1",
            [],
            Manga::class,
        );

        return $manga;
    }

    public function findLastAddedDto(): ?MangaStatsData
    {
        $manga = $this->findLastAdded();

        if ($manga === null)
        {
            return null;
        }

        return $this->mapToStatsDto($manga);
    }

    public function findLongestSeries(): ?Manga
    {
        /** @var Manga|null $manga */
        $manga = $this->fetchOne(
            "SELECT
                m.*,
                stats.total

            FROM {$this->table()} m

            INNER JOIN (
                SELECT
                    slug,
                    COUNT(*) AS total

                FROM {$this->table()}

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

    public function findLongestSeriesDto(): ?MangaStatsData
    {
        $manga = $this->findLongestSeries();

        if ($manga === null)
        {
            return null;
        }

        return $this->mapToStatsDto($manga);
    }

    /**
     * @return list<MangaStatsData>
     */
    public function topLongestSeriesDto(
        int $limit = 5,
    ): array {

        return array_map(
            fn (Manga $manga)
                => $this->mapToStatsDto($manga),
            $this->topLongestSeries($limit),
        );
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

            FROM {$this->table()} m

            INNER JOIN (
                SELECT
                    slug,
                    COUNT(*) AS total

                FROM {$this->table()}

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

    public function isSeriesCompleted(
        string $slug,
    ): bool {

        $result = $this->fetchOne(
            "
            SELECT
                COUNT(*) AS total,
                SUM(lu) AS total_lu
            FROM {$this->table()}
            WHERE slug = :slug
            ",
            [
                'slug' => Str::slug($slug),
            ],
        );

        if ($result === null)
        {
            return false;
        }

        return
            (int) $result->total > 0
            && (int) $result->total
                === (int) $result->total_lu;
    }

    public function countCompletedSeries(): int
    {
        return (int) $this->fetchSingleValue(
            "
            SELECT COUNT(*) AS total
            FROM (
                SELECT slug
                FROM {$this->table()}
                GROUP BY slug
                HAVING COUNT(*) = SUM(lu)
            ) completed
            ",
            'total',
        );
    }
}
