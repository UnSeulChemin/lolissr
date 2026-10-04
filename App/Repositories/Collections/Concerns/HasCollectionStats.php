<?php

declare(strict_types=1);

namespace App\Repositories\Collections\Concerns;

trait HasCollectionStats
{
    public function countCollected(): int
    {
        $row = $this->fetchOne("SELECT COUNT(*) AS total FROM {$this->readTable()} WHERE collect = 1");
        return (int) ($row->total ?? 0);
    }
}
