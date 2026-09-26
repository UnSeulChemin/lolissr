<?php

declare(strict_types=1);

namespace App\Repositories\Manga;

use App\DTO\Manga\Responses\MangaStatsData;
use App\Models\Manga;
use App\Models\Model;

use Framework\Support\Str;

final class MangaStatsRepository extends Model
{
    /** @return array{read: int, rewarded_tomes: int, rewarded_series: int} */
    public function profileSummary(): array
    {
        $row = $this->fetchOne(
            "SELECT COUNT(CASE WHEN lu = 1 THEN 1 END) AS total_read,
                COUNT(CASE WHEN xp_read_rewarded = 1 THEN 1 END) AS rewarded_tomes,
                COUNT(DISTINCT CASE WHEN xp_series_rewarded = 1 THEN slug END) AS rewarded_series
            FROM {$this->table()}"
        );

        return [
            'read' => (int) ($row->total_read ?? 0),
            'rewarded_tomes' => (int) ($row->rewarded_tomes ?? 0),
            'rewarded_series' => (int) ($row->rewarded_series ?? 0),
        ];
    }

    /** @return array{total: int, series: int, read: int, average: float|null} */
    public function dashboardSummary(): array
    {
        $row = $this->fetchOne("SELECT COUNT(*) AS total, COUNT(DISTINCT slug) AS series,
            COALESCE(SUM(CASE WHEN lu = 1 THEN 1 ELSE 0 END), 0) AS total_read,
            ROUND(AVG(note), 1) AS average_note FROM {$this->table()}");
        return ['total' => (int) ($row->total ?? 0), 'series' => (int) ($row->series ?? 0),
            'read' => (int) ($row->total_read ?? 0),
            'average' => isset($row->average_note) ? (float) $row->average_note : null];
    }

    protected string $table = 'manga';

    public function countAllTomes(): int
    {
        return (int) $this->fetchSingleValue("SELECT COUNT(*) AS total FROM {$this->table()}", 'total');
    }

    public function countSeries(): int
    {
        return (int) $this->fetchSingleValue("SELECT COUNT(DISTINCT slug) AS total FROM {$this->table()}", 'total');
    }

    public function countRead(): int
    {
        return (int) $this->fetchSingleValue("SELECT COUNT(*) AS total FROM {$this->table()} WHERE lu = 1", 'total');
    }

    public function countRewardedTomes(): int
    {
        return (int) $this->fetchSingleValue(
            "SELECT COUNT(*) AS total FROM {$this->table()} WHERE xp_read_rewarded = 1",
            'total'
        );
    }

    public function countRewardedSeries(): int
    {
        return (int) $this->fetchSingleValue(
            "SELECT COUNT(DISTINCT slug) AS total FROM {$this->table()} WHERE xp_series_rewarded = 1",
            'total'
        );
    }

    public function averageNote(): ?float
    {
        $average = $this->fetchSingleValue(
            "
            SELECT
                ROUND(AVG(note), 1) AS moyenne

            FROM {$this->table()}

            WHERE note IS NOT NULL
            ",
            'moyenne',
            [],
            null
        );

        return $average !== null ? (float) $average : null;
    }

    public function findLastAdded(): ?Manga
    {
        /** @var Manga|null $manga */
        $manga = $this->fetchOne(
            "
            SELECT *

            FROM {$this->table()}

            ORDER BY id DESC

            LIMIT 1
            ",
            [],
            Manga::class
        );

        return $manga;
    }

    public function findLastAddedDto(): ?MangaStatsData
    {
        $manga = $this->findLastAdded();

        return $manga !== null
            ? $this->mapToStatsDto($manga, true)
            : null;
    }

    public function findLongestSeries(): ?Manga
    {
        return $this->topLongestSeries(1)[0] ?? null;
    }

    public function findLongestSeriesDto(): ?MangaStatsData
    {
        $manga = $this->findLongestSeries();

        return $manga !== null
            ? $this->mapToStatsDto($manga)
            : null;
    }

    /**
     * @return list<MangaStatsData>
     */
    public function topLongestSeriesDto(int $limit = 5): array
    {
        return array_map(
            fn (Manga $manga) => $this->mapToStatsDto($manga),
            $this->topLongestSeries($limit),
        );
    }

    /**
     * @return list<Manga>
     */
    public function topLongestSeries(int $limit = 5): array
    {
        $limit = max(1, $limit);

        $mangas = $this->fetchAll(
            "
            SELECT m.*, stats.total
            FROM {$this->table()} m
            INNER JOIN (
                SELECT slug, COUNT(*) AS total
                FROM {$this->table()}
                GROUP BY slug
            ) stats ON stats.slug = m.slug
            WHERE m.id = (
                SELECT first_tome.id
                FROM {$this->table()} first_tome
                WHERE first_tome.slug = m.slug
                ORDER BY first_tome.numero ASC, first_tome.id ASC
                LIMIT 1
            )
            ORDER BY stats.total DESC, m.livre ASC, m.slug ASC, m.id ASC
            LIMIT {$limit}
            ",
            [],
            Manga::class
        );

        return $mangas;
    }

    public function isSeriesCompleted(string $slug): bool
    {
        $result = $this->fetchOne(
            "
            SELECT
                COUNT(*) AS total,
                SUM(lu) AS total_lu,
                MAX(CASE WHEN numero = 1 AND statut = 'termine' THEN 1 ELSE 0 END) AS termine

            FROM {$this->table()}

            WHERE slug = :slug
            ",
            [
                'slug' => Str::slug($slug),
            ]
        );

        if ($result === null)
        {
            return false;
        }

        /** @var array{total?: mixed, total_lu?: mixed, termine?: mixed} $data */
        $data = (array) $result;

        $total = (int) ($data['total'] ?? 0);
        $totalLu = (int) ($data['total_lu'] ?? 0);
        // The collection uses volume 1 as the reference for the series status.
        $isFinished = (int) ($data['termine'] ?? 0) === 1;

        return $isFinished && $total > 0 && $total === $totalLu;
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
            'total'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    private function mapToStatsDto(
        Manga $manga,
        bool $linkToTome = false,
    ): MangaStatsData
    {
        $thumbnailUrl = 'images/manga/placeholder-manga.webp';

        if (
            $manga->thumbnail !== ''
            && $manga->extension !== ''
        )
        {
            $thumbnailUrl =
                'images/manga/thumbnail/'
                . $manga->thumbnail
                . '.'
                . $manga->extension;
        }

        $url =
            'manga/series/'
            . rawurlencode($manga->slug);

        if ($linkToTome)
        {
            $url .= '/' . $manga->numero;
        }

        return new MangaStatsData(
            id: $manga->id,
            slug: $manga->slug,
            livre: $manga->livre,

            thumbnailUrl: $thumbnailUrl,
            url: $url,

            numero: $manga->numero,

            numeroLabel:
                'Tome '
                . str_pad(
                    (string) $manga->numero,
                    2,
                    '0',
                    STR_PAD_LEFT,
                ),

            total: $manga->total,

            totalLabel:
                ($manga->total ?? 0)
                . ' tomes',
        );
    }
}
