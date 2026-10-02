<?php

declare(strict_types=1);

namespace App\Repositories\Peluche;

use App\Repositories\AbstractRepository;
use App\Repositories\Collections\Concerns\HasCollectionStats;

final class PelucheStatsRepository extends AbstractRepository
{
    use HasCollectionStats;

    protected string $table = 'peluche';
}
