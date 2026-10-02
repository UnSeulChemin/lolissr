<?php

declare(strict_types=1);

namespace App\Repositories\Figurine;

use App\Repositories\AbstractRepository;
use App\Repositories\Collections\Concerns\HasCollectionStats;

final class FigurineStatsRepository extends AbstractRepository
{
    use HasCollectionStats;

    protected string $table = 'figurine';
}
