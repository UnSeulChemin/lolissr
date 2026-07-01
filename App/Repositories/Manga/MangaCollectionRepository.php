<?php

declare(strict_types=1);

namespace App\Repositories\Manga;

use App\Models\Manga;
use App\Models\Model;
use App\Repositories\Manga\Concerns\HasMangaStatsSubQuery;

final class MangaCollectionRepository extends Model
{
    use HasMangaStatsSubQuery;

    protected string $table = 'manga';

    private const ALLOWED_ORDER_BY = [
        'id DESC',
        'id ASC',
    ];

    public function countFirstTomes(): int
    {
        $result = $this->fetchOne(
            "SELECT COUNT(*) AS total FROM {$this->table()} WHERE numero = 1"
        );

        return (int) ($result->total ?? 0);
    }

    /**
     * @return list<Manga>
     */
    public function findAllFirstTomes(string $orderBy, int $perPage, int $page): array
    {
        $page = max(1, $page);
        $perPage = max(1, $perPage);
        $offset = ($page - 1) * $perPage;

        if (! in_array($orderBy, self::ALLOWED_ORDER_BY, true))
        {
            $orderBy = 'id DESC';
        }

        /** @var list<Manga> $mangas */
        $mangas = $this->fetchAll(
            "
            SELECT
                m.*,
                stats.total,
                stats.total_lu,
                stats.average_note

            FROM {$this->table()} m

            INNER JOIN (
                {$this->statsSubQuery()}
            ) stats
                ON stats.slug = m.slug

            WHERE m.numero = 1

            ORDER BY
                CASE WHEN stats.total_lu < stats.total THEN 0 ELSE 1 END ASC,
                CASE WHEN m.statut = 'termine' THEN 1 ELSE 0 END ASC,
                stats.average_note ASC,
                {$orderBy}

            LIMIT {$perPage}
            OFFSET {$offset}
            ",
            [],
            Manga::class
        );

        return $mangas;
    }

    /**
     * @return list<Manga>
     */
    public function findSeriesWithoutPerfectNote(): array
    {
        /** @var list<Manga> $mangas */
        $mangas = $this->fetchAll(
            "
            SELECT
                m.*,
                stats.total,
                stats.total_lu,
                stats.average_note

            FROM {$this->table()} m

            INNER JOIN (
                {$this->statsSubQuery()}
            ) stats
                ON stats.slug = m.slug

            WHERE m.numero = 1
            AND stats.average_note < 10

            ORDER BY
                stats.average_note ASC,
                m.livre ASC
            ",
            [],
            Manga::class
        );

        return $mangas;
    }

    /**
     * @return list<Manga>
     */
    public function findIncompleteSeries(): array
    {
        /** @var list<Manga> $mangas */
        $mangas = $this->fetchAll(
            "
            SELECT
                m.*,
                stats.total,
                stats.total_lu,
                stats.average_note

            FROM {$this->table()} m

            INNER JOIN (
                {$this->statsSubQuery()}
            ) stats
                ON stats.slug = m.slug

            WHERE m.numero = 1
            AND stats.total_lu < stats.total

            ORDER BY
                m.livre ASC
            ",
            [],
            Manga::class
        );

        return $mangas;
    }
}
