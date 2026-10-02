<?php

declare(strict_types=1);

namespace App\Repositories\Manga;

use App\Models\Manga;
use App\Repositories\AbstractRepository;
use App\Repositories\Manga\Concerns\HasMangaStatsSubQuery;

final class MangaCollectionRepository extends AbstractRepository
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
            "SELECT COUNT(DISTINCT slug) AS total FROM {$this->table()}"
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
                m.id, m.slug, m.numero, m.livre, m.thumbnail, m.extension, m.statut, m.note, m.lu,
                stats.total,
                stats.total_lu,
                stats.average_note

            FROM {$this->table()} m

            INNER JOIN (
                {$this->statsSubQuery()}
            ) stats
                ON stats.slug = m.slug

            WHERE m.id = (
                SELECT first_tome.id
                FROM {$this->table()} first_tome
                WHERE first_tome.slug = m.slug
                ORDER BY first_tome.numero ASC, first_tome.id ASC
                LIMIT 1
            )

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

    /** @return array{mangas: list<Manga>, total: int} */
    public function filteredPage(bool $notes, int $perPage, int $page): array
    {
        $perPage = max(1, $perPage);
        $pageIndex = max(1, $page) - 1;
        // Conserver un OFFSET entier même pour un paramètre de route extrême.
        // Le total retourné permet au service de refuser une page inexistante.
        $offset = $pageIndex > intdiv(PHP_INT_MAX, $perPage)
            ? PHP_INT_MAX
            : $pageIndex * $perPage;
        $condition = $notes ? 'average_note < 10' : 'total_lu < total';
        $order = $notes ? 'average_note ASC, livre ASC, id ASC' : 'livre ASC, id ASC';
        // La CTE groupée sert au comptage et à la pagination. La jointure gauche
        // préserve le total lorsque la page ou la liste demandée est vide.
        $rows = $this->fetchAll("WITH stats AS ({$this->statsSubQuery()}),
            filtered AS (SELECT * FROM stats WHERE $condition),
            paged AS (
                SELECT m.id, m.slug, m.numero, m.livre, m.thumbnail, m.extension,
                    m.statut, m.note, m.lu, filtered.total, filtered.total_lu, filtered.average_note
                FROM filtered INNER JOIN {$this->table()} m ON m.id = (
                    SELECT first_tome.id FROM {$this->table()} first_tome
                    WHERE first_tome.slug = filtered.slug
                    ORDER BY first_tome.numero ASC, first_tome.id ASC LIMIT 1
                )
                ORDER BY $order LIMIT $perPage OFFSET $offset
            )
            SELECT paged.*, totals.matching_series
            FROM (SELECT COUNT(*) AS matching_series FROM filtered) totals
            LEFT JOIN paged ON 1 = 1 ORDER BY $order");
        $mangas = [];
        foreach ($rows as $row)
        {
            if ($row->id === null) continue;
            $manga = new Manga();
            $manga->id = (int) $row->id;
            $manga->slug = (string) $row->slug;
            $manga->numero = (int) $row->numero;
            $manga->livre = (string) $row->livre;
            $manga->thumbnail = (string) $row->thumbnail;
            $manga->extension = (string) $row->extension;
            $manga->statut = (string) $row->statut;
            $manga->note = $row->note === null ? null : (int) $row->note;
            $manga->lu = (bool) $row->lu;
            $manga->total = (int) $row->total;
            $manga->total_lu = (int) $row->total_lu;
            $manga->average_note = (float) $row->average_note;
            $mangas[] = $manga;
        }

        return ['mangas' => $mangas, 'total' => (int) ($rows[0]->matching_series ?? 0)];
    }
}
