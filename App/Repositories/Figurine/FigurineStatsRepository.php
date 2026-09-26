<?php

declare(strict_types=1);

namespace App\Repositories\Figurine;

use App\Models\Model;
use App\Repositories\Collections\Concerns\HasCollectionStats;

final class FigurineStatsRepository extends Model
{
    use HasCollectionStats;

    protected string $table = 'figurine';
}
