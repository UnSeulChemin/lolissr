<?php

declare(strict_types=1);

namespace App\Repositories\Manga;

use App\Core\Support\Str;
use App\Models\Manga;
use App\Models\Model;

final class MangaSearchRepository extends Model
{
    protected string $table = 'manga';

    /**
     * @return list<Manga>
     */
    public function searchMangas(
        string $search
    ): array {
        $search = trim(
            preg_replace(
                '/\s+/',
                ' ',
                trim($search)
            ) ?? ''
        );

        if ($search === '') {
            return [];
        }

        $pattern = '/^(.*?)(?:\s+(?:t|tome|vol|vol\.|volume|n°|no|#)?\s*0*([1-9][0-9]*))$/iu';

        if (
            preg_match(
                $pattern,
                $search,
                $matches
            )
        ) {
            $titlePart = trim($matches[1]);

            $numero = (int) $matches[2];

            if ($titlePart !== '') {
                return $this->fetchAll(
                    "SELECT *
                    FROM {$this->getTable()}
                    WHERE (
                        livre LIKE :search_livre
                        OR slug LIKE :search_slug
                    )
                    AND numero = :numero
                    ORDER BY livre ASC, numero ASC",
                    [
                        'search_livre' =>
                            "%{$titlePart}%",

                        'search_slug' =>
                            '%'
                            . Str::slug($titlePart)
                            . '%',

                        'numero' => $numero,
                    ],
                    Manga::class
                );
            }
        }

        return $this->fetchAll(
            "SELECT *
            FROM {$this->getTable()}
            WHERE livre LIKE :search_livre
            OR slug LIKE :search_slug
            ORDER BY livre ASC, numero ASC",
            [
                'search_livre' =>
                    "%{$search}%",

                'search_slug' =>
                    '%'
                    . Str::slug($search)
                    . '%',
            ],
            Manga::class
        );
    }

    public function countFirstTomesPaginate(
        int $eachPerPage
    ): int {
        $eachPerPage = max(1, $eachPerPage);

        $result = $this->fetchOne(
            "SELECT COUNT(*) AS total
            FROM {$this->getTable()}
            WHERE numero = 1"
        );

        $total = (int) ($result->total ?? 0);

        return max(
            1,
            (int) ceil($total / $eachPerPage)
        );
    }

    /**
     * @return list<Manga>
     */
    public function findAllFirstTomes(
        string $orderBy,
        int $eachPerPage,
        int $page
    ): array {
        $page = max(1, $page);

        $eachPerPage = max(1, $eachPerPage);

        $start =
            ($page - 1)
            * $eachPerPage;

        $allowedOrderBy = [
            'id DESC',
            'id ASC',
        ];

        if (
            !in_array(
                $orderBy,
                $allowedOrderBy,
                true
            )
        ) {
            $orderBy = 'id DESC';
        }

        return $this->fetchAll(
            "SELECT m.*,
                    stats.total,
                    stats.total_lu,
                    stats.average_note
            FROM {$this->getTable()} m
            INNER JOIN (
                SELECT slug,
                       COUNT(*) AS total,
                       SUM(
                           CASE
                               WHEN lu = 1 THEN 1
                               ELSE 0
                           END
                       ) AS total_lu,
                       ROUND(
                           AVG(COALESCE(note, 0)),
                           1
                       ) AS average_note
                FROM {$this->getTable()}
                GROUP BY slug
            ) stats ON stats.slug = m.slug
            WHERE m.numero = 1
            ORDER BY
                CASE
                    WHEN stats.total_lu < stats.total
                    THEN 0
                    ELSE 1
                END ASC,

                CASE
                    WHEN m.statut = 'termine'
                    THEN 1
                    ELSE 0
                END ASC,

                stats.average_note ASC,
                {$orderBy}

            LIMIT {$start}, {$eachPerPage}",
            [],
            Manga::class
        );
    }
}
