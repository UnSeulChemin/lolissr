<?php

declare(strict_types=1);

final class Stats
{
    private int $total = 0;

    private int $success = 0;

    private int $fail = 0;

    private float $duration = 0.0;

    private function addDuration(
        float $duration,
    ): void {
        $this->total++;
        $this->duration += $duration;
    }

    public function success(
        float $duration,
    ): void {

        $this->addDuration(
            $duration,
        );

        $this->success++;
    }

    public function fail(
        float $duration,
    ): void {

        $this->addDuration(
            $duration,
        );

        $this->fail++;
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

    public function successRate(): float
    {
        if ($this->total === 0)
        {
            return 0.0;
        }

        return round(
            ($this->success / $this->total) * 100,
            2,
        );
    }

    public function averageDuration(): float
    {
        if ($this->total === 0)
        {
            return 0.0;
        }

        return
            $this->duration
            / $this->total;
    }

    public function hasFailures(): bool
    {
        return $this->fail > 0;
    }
}