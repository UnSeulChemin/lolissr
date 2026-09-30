<?php

declare(strict_types=1);

namespace App\Repositories\Collections\Concerns;

trait HasCollectionStats
{
    public function countCollected(): int
    {
        $row = $this->fetchOne("SELECT COUNT(*) AS total FROM {$this->table()} WHERE collect = 1");
        return (int) ($row->total ?? 0);
    }
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

}
