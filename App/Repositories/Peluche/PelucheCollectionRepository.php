<?php

declare(strict_types=1);

namespace App\Repositories\Peluche;

use App\Models\Peluche;
use App\Repositories\AbstractRepository;

final class PelucheCollectionRepository extends AbstractRepository
{
    protected string $table = 'peluche';

    public function countAll(): int
    {
        return $this->countRows();
    }

    /**
     * @return list<Peluche>
     */
    public function findPaginated(int $limit, int $page): array
    {
        $page = max(1, $page);
        $limit = max(1, $limit);

        $offset = ($page - 1) * $limit;

        /** @var list<Peluche> $peluches */
        $peluches = $this->fetchAll(
            "
            SELECT p.slug, p.numero, p.waifu, p.origin, p.thumbnail, p.extension, p.collect

            FROM {$this->readTable('p')}

            INNER JOIN (
                SELECT
                    slug,
                    MAX(id) AS last_id

                FROM {$this->readTable()}

                GROUP BY slug
            ) grouped
                ON grouped.slug = p.slug

            ORDER BY
                grouped.last_id DESC,
                p.numero DESC

            LIMIT :limit
            OFFSET :offset
            ",
            ['limit' => $limit, 'offset' => $offset],
            Peluche::class
        );

        return $peluches;
    }
}
