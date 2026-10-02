<?php

declare(strict_types=1);

namespace App\Repositories\Nendoroid;

use App\Repositories\AbstractRepository;
use App\Repositories\Collections\Concerns\HasCollectionStats;

final class NendoroidStatsRepository extends AbstractRepository
{
    use HasCollectionStats;

    protected string $table = 'nendoroid';
}
