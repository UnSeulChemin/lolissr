<?php

declare(strict_types=1);

namespace App\Repositories\Figurine;

use App\Models\Model;

final class FigurineStatsRepository extends Model
{
    protected string $table = 'figurine';

    public function countCollected(): int
    {
        return (int) $this->fetchSingleValue(
            "SELECT COUNT(*) AS total FROM {$this->table()}",
            'total',
        );
    }
}