<?php

declare(strict_types=1);

namespace App\Repositories\Nendoroid;

use App\Models\Model;

final class NendoroidStatsRepository extends Model
{
    protected string $table = 'nendoroid';

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