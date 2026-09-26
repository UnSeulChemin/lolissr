<?php

declare(strict_types=1);

namespace App\Repositories\Peluche;

use App\Models\Model;
use App\Repositories\Collections\Concerns\HasCollectionStats;

final class PelucheStatsRepository extends Model
{
    use HasCollectionStats;

    protected string $table = 'peluche';
}
