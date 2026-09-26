<?php

declare(strict_types=1);

namespace App\Repositories\Nendoroid;

use App\Models\Model;
use App\Repositories\Collections\Concerns\HasCollectionStats;

final class NendoroidStatsRepository extends Model
{
    use HasCollectionStats;

    protected string $table = 'nendoroid';
}
