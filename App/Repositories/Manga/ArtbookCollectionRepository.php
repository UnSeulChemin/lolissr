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
            SELECT *

            FROM {$this->table()}

            ORDER BY created_at DESC
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
            SELECT *

            FROM {$this->table()}

            ORDER BY created_at DESC

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
