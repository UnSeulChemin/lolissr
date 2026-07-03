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
            SELECT *

            FROM {$this->table()}

            ORDER BY waifu ASC, numero ASC
            ",
            [],
            Figurine::class
        );

        return $figurines;
    }

    /**
     * @return list<Figurine>
     */
    public function findPaginated(int $limit, int $page): array
    {
        $offset = ($page - 1) * $limit;

        /** @var list<Figurine> $figurines */
        $figurines = $this->fetchAll(
            "
            SELECT *

            FROM {$this->table()}

            ORDER BY waifu ASC, numero ASC

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
