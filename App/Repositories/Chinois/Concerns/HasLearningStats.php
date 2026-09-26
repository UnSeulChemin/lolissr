<?php

declare(strict_types=1);

namespace App\Repositories\Chinois\Concerns;

trait HasLearningStats
{
    /** @return array{mastered: int, rewarded: int} */
    public function profileSummary(): array
    {
        $row = $this->fetchOne(
            "SELECT COUNT(CASE WHEN maitrise = 1 THEN 1 END) AS mastered,
                COUNT(CASE WHEN xp_rewarded = 1 THEN 1 END) AS rewarded
            FROM {$this->table()}"
        );

        return [
            'mastered' => (int) ($row->mastered ?? 0),
            'rewarded' => (int) ($row->rewarded ?? 0),
        ];
    }

    /** @return array{total: int, remaining: int} */
    public function dashboardSummary(): array
    {
        $row = $this->fetchOne("SELECT COUNT(*) AS total,
            COALESCE(SUM(CASE WHEN maitrise = 0 THEN 1 ELSE 0 END), 0) AS remaining FROM {$this->table()}");
        return ['total' => (int) ($row->total ?? 0), 'remaining' => (int) ($row->remaining ?? 0)];
    }

    // =========================================
    // STATISTIQUES
    // =========================================

    public function countAll(): int
    {
        return $this->countRows();
    }

    public function countRemaining(): int
    {
        return $this->countWhere('maitrise = 0');
    }

    public function countMastered(): int
    {
        return $this->countWhere('maitrise = 1');
    }

    public function countRewarded(): int
    {
        return $this->countWhere('xp_rewarded = 1');
    }
}
