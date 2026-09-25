<?php

declare(strict_types=1);

namespace App\Repositories\Peluche;

use App\Models\Model;

final class PelucheStatsRepository extends Model
{
    protected string $table = 'peluche';

    public function countRewarded(): int
    {
        return (int) $this->fetchSingleValue(
            "SELECT COUNT(*) AS total FROM {$this->table()} WHERE collect_rewarded = 1",
            'total'
        );
    }

    public function countCollected(): int
    {
        return (int) $this->fetchSingleValue(
            "
            SELECT COUNT(*) AS total

            FROM {$this->table()}

            WHERE collect = 1
            ",
            'total',
        );
    }
}
