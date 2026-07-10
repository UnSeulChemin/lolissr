<?php

declare(strict_types=1);

namespace App\Repositories\Nendoroid;

use App\Models\Model;
use App\Models\Nendoroid;

final class NendoroidCollectionRepository extends Model
{
    protected string $table = 'nendoroid';

    public function countAll(): int
    {
        return $this->countRows();
    }

    /**
     * @return list<Nendoroid>
     */
    public function findAll(): array
    {
        /** @var list<Nendoroid> $nendoroids */
        $nendoroids = $this->fetchAll(
            "
            SELECT n.*

            FROM {$this->table()} n

            INNER JOIN (
                SELECT
                    slug,
                    MAX(id) AS last_id

                FROM {$this->table()}

                GROUP BY slug
            ) grouped
                ON grouped.slug = n.slug

            ORDER BY
                grouped.last_id DESC,
                n.numero DESC
            ",
            [],
            Nendoroid::class
        );

        return $nendoroids;
    }

    /**
     * @return list<Nendoroid>
     */
    public function findPaginated(
        int $limit,
        int $page
    ): array
    {
        $page = max(1, $page);
        $limit = max(1, $limit);

        $offset = ($page - 1) * $limit;

        /** @var list<Nendoroid> $nendoroids */
        $nendoroids = $this->fetchAll(
            "
            SELECT n.*

            FROM {$this->table()} n

            INNER JOIN (
                SELECT
                    slug,
                    MAX(id) AS last_id

                FROM {$this->table()}

                GROUP BY slug
            ) grouped
                ON grouped.slug = n.slug

            ORDER BY
                grouped.last_id DESC,
                n.numero DESC

            LIMIT :limit
            OFFSET :offset
            ",
            [
                'limit' => $limit,
                'offset' => $offset,
            ],
            Nendoroid::class
        );

        return $nendoroids;
    }
}