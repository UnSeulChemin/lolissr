<?php

declare(strict_types=1);

namespace App\Repositories\Figurine;

use App\Models\Figurine;
use App\Models\Model;

final class FigurineCollectionRepository extends Model
{
    protected string $table = 'figurine';

    public function countAll(): int
    {
        return $this->countRows();
    }

    /**
     * @return list<Figurine>
     */
    public function findAll(): array
    {
        /** @var list<Figurine> $figurines */
        $figurines = $this->fetchAll(
            "
            SELECT f.*

            FROM {$this->table()} f

            INNER JOIN (
                SELECT
                    slug,
                    MAX(id) AS last_id

                FROM {$this->table()}

                GROUP BY slug
            ) grouped
                ON grouped.slug = f.slug

            ORDER BY
                grouped.last_id DESC,
                f.numero DESC
            ",
            [],
            Figurine::class
        );

        return $figurines;
    }

    /**
     * @return list<Figurine>
     */
    public function findPaginated(
        int $limit,
        int $page
    ): array
    {
        $page = max(1, $page);
        $limit = max(1, $limit);

        $offset = ($page - 1) * $limit;

        /** @var list<Figurine> $figurines */
        $figurines = $this->fetchAll(
            "
            SELECT f.*

            FROM {$this->table()} f

            INNER JOIN (
                SELECT
                    slug,
                    MAX(id) AS last_id

                FROM {$this->table()}

                GROUP BY slug
            ) grouped
                ON grouped.slug = f.slug

            ORDER BY
                grouped.last_id DESC,
                f.numero DESC

            LIMIT :limit
            OFFSET :offset
            ",
            [
                'limit' => $limit,
                'offset' => $offset,
            ],
            Figurine::class
        );

        return $figurines;
    }
}