<?php

declare(strict_types=1);

namespace App\Repositories\Figurine;

use App\Models\Figurine\Figurine;
use App\Repositories\AbstractRepository;
use App\Repositories\Collections\Concerns\SearchesCollectibles;

final class FigurineSearchRepository extends AbstractRepository
{
    use SearchesCollectibles;

    protected string $table = 'figurine';

    /** @return list<Figurine> */
    public function search(string $search, int $limit = 20): array
    {
        return $this->searchCollectibles($search, Figurine::class, $limit);
    }
}
