<?php

declare(strict_types=1);

namespace App\Repositories\Chinois\Concerns;

trait HasLearningStats
{
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
