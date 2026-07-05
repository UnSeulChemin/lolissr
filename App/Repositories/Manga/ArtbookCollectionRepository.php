<?php

declare(strict_types=1);

namespace App\Repositories\Manga;

use App\Models\Artbook;
use App\Models\Model;

final class ArtbookCollectionRepository extends Model
{
    protected string $table = 'artbook';

    /**
     * @return list<Artbook>
     */
    public function findAll(): array
    {
        /** @var list<Artbook> $artbooks */
        $artbooks = $this->fetchAll(
            "
            SELECT a.*

            FROM {$this->table()} a

            INNER JOIN (
                SELECT
                    slug,
                    MAX(id) AS last_id

                FROM {$this->table()}

                GROUP BY slug
            ) grouped
                ON grouped.slug = a.slug

            ORDER BY
                grouped.last_id DESC,
                a.numero DESC
            ",
            [],
            Artbook::class
        );

        return $artbooks;
    }

    /**
     * @return list<Artbook>
     */
    public function findPaginated(
        int $limit,
        int $page,
    ): array
    {
        $page = max(1, $page);
        $limit = max(1, $limit);

        $offset = ($page - 1) * $limit;

        /** @var list<Artbook> $artbooks */
        $artbooks = $this->fetchAll(
            "
            SELECT a.*

            FROM {$this->table()} a

            INNER JOIN (
                SELECT
                    slug,
                    MAX(id) AS last_id

                FROM {$this->table()}

                GROUP BY slug
            ) grouped
                ON grouped.slug = a.slug

            ORDER BY
                grouped.last_id DESC,
                a.numero DESC

            LIMIT :limit
            OFFSET :offset
            ",
            [
                'limit' => $limit,
                'offset' => $offset,
            ],
            Artbook::class
        );

        return $artbooks;
    }
}