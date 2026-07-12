<?php

declare(strict_types=1);

namespace App\Repositories\Peluche;

use App\Models\Model;
use App\Models\Peluche;

final class PelucheCollectionRepository extends Model
{
    protected string $table = 'peluche';

    public function countAll(): int
    {
        return $this->countRows();
    }

    /**
     * @return list<Peluche>
     */
    public function findAll(): array
    {
        /** @var list<Peluche> $peluches */
        $peluches = $this->fetchAll(
            "
            SELECT p.*

            FROM {$this->table()} p

            INNER JOIN (
                SELECT
                    slug,
                    MAX(id) AS last_id

                FROM {$this->table()}

                GROUP BY slug
            ) grouped
                ON grouped.slug = p.slug

            ORDER BY
                grouped.last_id DESC,
                p.numero DESC
            ",
            [],
            Peluche::class
        );

        return $peluches;
    }

    /**
     * @return list<Peluche>
     */
    public function findPaginated(
        int $limit,
        int $page
    ): array
    {
        $page = max(1, $page);
        $limit = max(1, $limit);

        $offset = ($page - 1) * $limit;

        /** @var list<Peluche> $peluches */
        $peluches = $this->fetchAll(
            "
            SELECT p.*

            FROM {$this->table()} p

            INNER JOIN (
                SELECT
                    slug,
                    MAX(id) AS last_id

                FROM {$this->table()}

                GROUP BY slug
            ) grouped
                ON grouped.slug = p.slug

            ORDER BY
                grouped.last_id DESC,
                p.numero DESC

            LIMIT :limit
            OFFSET :offset
            ",
            [
                'limit' => $limit,
                'offset' => $offset,
            ],
            Peluche::class
        );

        return $peluches;
    }
}