<?php

declare(strict_types=1);

namespace App\Repositories\Figurine;

use App\Models\Model;

final class FigurineStatsRepository extends Model
{
    /** @return array{collected: int, rewarded: int} */
    public function profileSummary(): array
    {
        $row = $this->fetchOne(
            "SELECT COUNT(CASE WHEN collect = 1 THEN 1 END) AS collected,
                COUNT(CASE WHEN collect_rewarded = 1 THEN 1 END) AS rewarded
            FROM {$this->table()}"
        );

        return [
            'collected' => (int) ($row->collected ?? 0),
            'rewarded' => (int) ($row->rewarded ?? 0),
        ];
    }

    protected string $table = 'figurine';

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
