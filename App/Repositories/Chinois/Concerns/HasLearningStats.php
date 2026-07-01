<?php

declare(strict_types=1);

namespace App\Repositories\Chinois\Concerns;

trait HasLearningStats
{
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
}