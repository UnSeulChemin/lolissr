<?php

declare(strict_types=1);

final class HttpTestStatistics
{
    private int $total = 0;

    private int $success = 0;

    private int $fail = 0;

    private float $duration = 0.0;

    /** @param array<int, array<string, mixed>> $results
     * @return list<array{method: string, path: string, label: string, median: float, min: float, max: float, first: float, warmMedian: ?float, bytesMax: ?int, samples: int}>
     */
    public static function slowestReads(array $results): array
    {
        return array_slice(self::readTimings($results), 0, 10);
    }

    /** @param array<int, array<string, mixed>> $results
     * @return list<array{method: string, path: string, label: string, median: float, min: float, max: float, first: float, warmMedian: ?float, bytesMax: ?int, samples: int}>
     */
    public static function readTimings(array $results): array
    {
        $groups = [];
        foreach ($results as $result)
        {
            if ($result['method'] !== 'GET' || $result['status'] !== 'OK' || $result['http_status'] !== 200) continue;
            // Different representations of the same URL remain separate cases.
            $key = serialize([$result['method'], $result['path'], $result['label']]);
            $groups[$key]['result'] = $result;
            $groups[$key]['times'][] = (float) $result['duration'];
            if (isset($result['response_bytes'])) $groups[$key]['bytes'][] = (int) $result['response_bytes'];
        }
        $rows = [];
        foreach ($groups as $group)
        {
            $times = $group['times'];
            $first = $times[0];
            $warm = array_slice($times, 1);
            sort($warm, SORT_NUMERIC);
            $warmCount = count($warm);
            $warmMiddle = intdiv($warmCount, 2);
            sort($times, SORT_NUMERIC);
            $count = count($times);
            $middle = intdiv($count, 2);
            $rows[] = [
                'method' => $group['result']['method'], 'path' => $group['result']['path'],
                'label' => $group['result']['label'],
                'median' => $count % 2 === 0 ? ($times[$middle - 1] + $times[$middle]) / 2 : $times[$middle],
                'min' => min($times), 'max' => max($times), 'first' => $first,
                'warmMedian' => $warmCount === 0 ? null : ($warmCount % 2 === 0 ? ($warm[$warmMiddle - 1] + $warm[$warmMiddle]) / 2 : $warm[$warmMiddle]),
                'bytesMax' => isset($group['bytes']) ? max($group['bytes']) : null, 'samples' => $count
            ];
        }
        usort($rows, static fn (array $a, array $b): int => $b['median'] <=> $a['median']);
        return $rows;
    }

    // =========================================
    // ENREGISTREMENT
    // =========================================

    public function success(float $duration): void
    {
        $this->record($duration);

        $this->success++;
    }

    public function fail(float $duration): void
    {
        $this->record($duration);

        $this->fail++;
    }

    // =========================================
    // STATISTIQUES
    // =========================================

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

        return round(($this->success / $this->total) * 100, 2);
    }

    public function averageDuration(): float
    {
        if ($this->total === 0)
        {
            return 0.0;
        }

        return $this->duration / $this->total;
    }

    public function hasFailures(): bool
    {
        return $this->fail > 0;
    }

    // =========================================
    // HELPERS
    // =========================================

    private function record(float $duration): void
    {
        $this->total++;
        $this->duration += max(0.0, $duration);
    }
}
