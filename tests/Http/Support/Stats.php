<?php

declare(strict_types=1);

final class Stats
{
    private int $total = 0;

    private int $success = 0;

    private int $fail = 0;

    private float $duration = 0.0;

    public function success(float $duration): void
    {
        $this->total++;
        $this->success++;
        $this->duration += $duration;
    }

    public function fail(float $duration): void
    {
        $this->total++;
        $this->fail++;
        $this->duration += $duration;
    }

    public function total(): int
    {
        return $this->total;
    }

    public function successCount(): int
    {
        return $this->success;
    }

    public function failCount(): int
    {
        return $this->fail;
    }

    public function duration(): float
    {
        return $this->duration;
    }

    public function hasFailures(): bool
    {
        return $this->fail > 0;
    }

    public function successRate(): float
    {
        if ($this->total === 0)
        {
            return 0;
        }

        return round(
            ($this->success / $this->total) * 100,
            2,
        );
    }
}