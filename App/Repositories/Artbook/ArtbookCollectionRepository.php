<?php

declare(strict_types=1);

namespace App\Repositories\Artbook;

use App\Models\Artbook;
use App\Repositories\AbstractRepository;

final class ArtbookCollectionRepository extends AbstractRepository
{
    protected string $table = 'artbook';

    /**
     * @return list<Artbook>
     */
    public function findPaginated(int $limit, int $page): array
    {
        $page = max(1, $page);
        $limit = max(1, $limit);

        $offset = ($page - 1) * $limit;

        /** @var list<Artbook> $artbooks */
        $artbooks = $this->fetchAll(
            "
            SELECT a.slug, a.numero, a.artbook, a.auteur, a.serie, a.thumbnail, a.extension

            FROM {$this->readTable('a')}

            INNER JOIN (
                SELECT
                    slug,
                    MAX(id) AS last_id

                FROM {$this->readTable()}

                GROUP BY slug
            ) grouped
                ON grouped.slug = a.slug

            ORDER BY
                grouped.last_id DESC,
                a.numero DESC

            LIMIT :limit
            OFFSET :offset
            ",
            ['limit' => $limit, 'offset' => $offset],
            Artbook::class
        );

        return $artbooks;
    }
}
