<?php

declare(strict_types=1);

namespace App\Repositories\Chinois\Concerns;

trait HasLearningStats
{
    public function countMastered(): int
    {
        $row = $this->fetchOne("SELECT COUNT(*) AS total FROM {$this->readTable()} WHERE maitrise = 1");
        return (int) ($row->total ?? 0);
    }
    /** @return array{total: int, remaining: int} */
    public function dashboardSummary(): array
    {
        $row = $this->fetchOne("SELECT COUNT(*) AS total,
            COALESCE(SUM(CASE WHEN maitrise = 0 THEN 1 ELSE 0 END), 0) AS remaining FROM {$this->readTable()}");
        return ['total' => (int) ($row->total ?? 0), 'remaining' => (int) ($row->remaining ?? 0)];
    }
}
