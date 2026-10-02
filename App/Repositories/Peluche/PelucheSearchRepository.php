<?php

declare(strict_types=1);

namespace App\Repositories\Peluche;

use App\Models\Peluche;
use App\Models\Model;
use App\Repositories\Collections\Concerns\SearchesCollectibles;

final class PelucheSearchRepository extends Model
{
    use SearchesCollectibles;

    protected string $table = 'peluche';

    /** @return list<Peluche> */
    public function search(string $search): array
    {
        return $this->searchCollectibles($search, Peluche::class);
    }
}
