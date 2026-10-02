<?php

declare(strict_types=1);

namespace App\Repositories\Manga;

use App\DTO\Manga\Responses\MangaStatsData;
use App\Models\Manga;
use App\Repositories\AbstractRepository;


final class MangaStatsRepository extends AbstractRepository
{
    public function countRead(): int
    {
        $row = $this->fetchOne("SELECT COUNT(*) AS total FROM {$this->table()} WHERE lu = 1");
        return (int) ($row->total ?? 0);
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

    public function findLastAdded(): ?Manga
    {
        /** @var Manga|null $manga */
        $manga = $this->fetchOne(
            "
            SELECT id, slug, numero, livre, thumbnail, extension

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
            SELECT m.id, m.slug, m.numero, m.livre, m.thumbnail, m.extension, stats.total
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

    public function countCompletedSeries(?int $limit = null): int
    {
        $limitSql = $limit === null ? '' : 'LIMIT ' . max(1, $limit);
        return (int) $this->fetchSingleValue(
            "
            SELECT COUNT(*) AS total

            FROM (
                SELECT slug

                FROM {$this->table()}

                GROUP BY slug

                HAVING COUNT(*) = SUM(lu)
                AND MAX(CASE WHEN numero = 1 AND statut = 'termine' THEN 1 ELSE 0 END) = 1
                {$limitSql}
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
