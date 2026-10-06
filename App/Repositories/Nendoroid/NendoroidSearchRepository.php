<?php

declare(strict_types=1);

namespace App\Repositories\Nendoroid;

use App\Models\Nendoroid\Nendoroid;
use App\Repositories\AbstractRepository;
use App\Repositories\Collections\Concerns\SearchesCollectibles;

final class NendoroidSearchRepository extends AbstractRepository
{
    use SearchesCollectibles;

    protected string $table = 'nendoroid';

    /** @return list<Nendoroid> */
    public function search(string $search): array
    {
        return $this->searchCollectibles($search, Nendoroid::class);
    }
}
