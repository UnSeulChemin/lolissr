<?php

declare(strict_types=1);

namespace App\Repositories\Figurine;

use App\Models\Figurine;
use App\Models\Model;
use App\Repositories\Collections\Concerns\SearchesCollectibles;

final class FigurineSearchRepository extends Model
{
    use SearchesCollectibles;

    protected string $table = 'figurine';

    /** @return list<Figurine> */
    public function search(string $search): array
    {
        return $this->searchCollectibles($search, Figurine::class);
    }
}
