<?php

declare(strict_types=1);

namespace App\Repositories\Manga;

use App\Models\Manga;
use App\Models\Model;
use Framework\Support\Str;

final class MangaSearchRepository extends Model
{
    protected string $table = 'manga';

    private function normalizeSearch(
        string $search,
    ): string {
        return trim(
            preg_replace(
                '/\s+/',
                ' ',
                trim($search),
            ) ?? '',
        );
    }

    private function slugSearch(
        string $search,
    ): string {
        return Str::slug($search);
    }

    /**
     * @return array{
     *     title: string,
     *     numero: int
     * }|null
     */
    private function extractSearchNumero(
        string $search,
    ): ?array
    {
        $pattern = '
            /^
            (.*?)
            \s*
            (?:
                t
                |tome
                |vol
                |vol\.
                |volume
                |n°
                |no
                |\#
            )?
            \s*
            0*
            ([1-9][0-9]*)
            $
            /ixu
        ';

        if (
            !preg_match(
                $pattern,
                $search,
                $matches,
            )
        ) {
            return null;
        }

        $title = trim(
            $matches[1] ?? '',
        );

        $numero = (int) (
            $matches[2]
            ?? 0
        );

        if (
            $title === ''
            || $numero < 1
        ) {
            return null;
        }

        return [
            'title' => $title,
            'numero' => $numero,
        ];
    }

    /**
     * @return list<Manga>
     */
    private function fetchSearchResults(
        string $title,
        ?int $numero = null,
    ): array {
        $sql = "
            SELECT *
            FROM {$this->getTable()}
            WHERE (
                livre LIKE :search_livre
                OR slug LIKE :search_slug
            )
        ";

        $params = [
            'search_livre' => "%{$title}%",
            'search_slug' => '%'
                . $this->slugSearch($title)
                . '%',
        ];

        if ($numero !== null) {
            $sql .= '
                AND numero = :numero
            ';

            $params['numero'] = $numero;
        }

        $sql .= '
            ORDER BY livre ASC, numero ASC
        ';

        /** @var list<Manga> $mangas */
        $mangas = $this->fetchAll(
            $sql,
            $params,
            Manga::class,
        );

        return $mangas;
    }

    /**
     * @return list<Manga>
     */
    public function searchMangas(
        string $search,
    ): array {
        $search = $this->normalizeSearch(
            $search,
        );

        if ($search === '') {
            return [];
        }

        $searchNumero = $this->extractSearchNumero(
            $search,
        );

        if ($searchNumero !== null) {
            return $this->fetchSearchResults(
                $searchNumero['title'],
                $searchNumero['numero'],
            );
        }

        return $this->fetchSearchResults(
            $search,
        );
    }

    public function countFirstTomes(): int
    {
        $result = $this->fetchOne(
            "SELECT COUNT(*) AS total
            FROM {$this->getTable()}
            WHERE numero = 1"
        );

        return (int) ($result->total ?? 0);
    }

    /**
     * @return list<Manga>
     */
    public function findAllFirstTomes(
        string $orderBy,
        int $eachPerPage,
        int $page,
    ): array {
        $page = max(
            1,
            $page,
        );

        $eachPerPage = max(
            1,
            $eachPerPage,
        );

        $offset =
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
                true,
            )
        ) {
            $orderBy = 'id DESC';
        }

        /** @var list<Manga> $mangas */
        $mangas = $this->fetchAll(
            "SELECT
                m.*,
                stats.total,
                stats.total_lu,
                stats.average_note

            FROM {$this->getTable()} m

            INNER JOIN (
                SELECT
                    slug,

                    COUNT(*) AS total,

                    SUM(
                        CASE
                            WHEN lu = 1
                            THEN 1
                            ELSE 0
                        END
                    ) AS total_lu,

                    ROUND(
                        AVG(
                            COALESCE(note, 0)
                        ),
                        1
                    ) AS average_note

                FROM {$this->getTable()}

                GROUP BY slug

            ) stats
                ON stats.slug = m.slug

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

            LIMIT {$offset}, {$eachPerPage}",
            [],
            Manga::class,
        );

        return $mangas;
    }
}