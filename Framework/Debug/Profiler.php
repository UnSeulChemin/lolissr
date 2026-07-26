<?php

declare(strict_types=1);

namespace Framework\Debug;

use Framework\Support\Logger;

final class Profiler
{
    private static bool $active = false;

    /**
     * @var array<string, int|float>
     */
    private static array $starts = [];

    /**
     * @var array<string, float>
     */
    private static array $durations = [];

    /**
     * @var array<string, int>
     */
    private static array $counters = [];

    private static int|float|null $requestStart = null;

    private function __construct()
    {
    }

    /*
    |--------------------------------------------------------------------------
    | REQUEST
    |--------------------------------------------------------------------------
    */

    public static function startRequest(): void
    {
        self::reset();

        if (! self::enabled())
        {
            return;
        }

        self::$active = true;
        self::$requestStart = hrtime(true);
    }

    public static function finishRequest(string $method, string $uri, int $status = 200): void
    {
        if (! self::$active || self::$requestStart === null)
        {
            return;
        }

        $total = self::elapsedMilliseconds(self::$requestStart);

        Logger::debug(
            '[PROFILER] ' . strtoupper($method) . ' ' . $uri,
            [
                'status' => $status,
                'total_ms' => round($total, 2),
                'durations_ms' => self::roundedDurations(),
                'counters' => self::$counters,
                'memory' => [
                    'current_mb' => round(memory_get_usage(true) / 1024 / 1024, 2),
                    'peak_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
                ],
            ]
        );

        self::reset();
    }

    /*
    |--------------------------------------------------------------------------
    | MEASURES
    |--------------------------------------------------------------------------
    */

    public static function start(string $name): void
    {
        if (! self::$active || $name === '')
        {
            return;
        }

        self::$starts[$name] = hrtime(true);
    }

    public static function end(string $name): float
    {
        if (! self::$active || $name === '')
        {
            return 0.0;
        }

        $start = self::$starts[$name] ?? null;

        if ($start === null)
        {
            return 0.0;
        }

        $duration = self::elapsedMilliseconds($start);

        self::$durations[$name] = (self::$durations[$name] ?? 0.0) + $duration;

        unset(self::$starts[$name]);

        return $duration;
    }

    /**
     * @template T
     *
     * @param callable(): T $callback
     *
     * @return T
     */
    public static function measure(string $name, callable $callback): mixed
    {
        if (! self::$active || $name === '')
        {
            return $callback();
        }

        self::start($name);

        try
        {
            return $callback();
        }
        finally
        {
            self::end($name);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | COUNTERS
    |--------------------------------------------------------------------------
    */

    public static function increment(string $name, int $amount = 1): void
    {
        if (! self::$active || $name === '' || $amount === 0)
        {
            return;
        }

        self::$counters[$name] = (self::$counters[$name] ?? 0) + $amount;
    }

    /*
    |--------------------------------------------------------------------------
    | DATA
    |--------------------------------------------------------------------------
    */

    public static function active(): bool
    {
        return self::$active;
    }

    /**
     * @return array<string, float>
     */
    public static function durations(): array
    {
        return self::$durations;
    }

    public static function duration(string $name): float
    {
        return self::$durations[$name] ?? 0.0;
    }

    /**
     * @return array<string, int>
     */
    public static function counters(): array
    {
        return self::$counters;
    }

    public static function counter(string $name): int
    {
        return self::$counters[$name] ?? 0;
    }

    /*
    |--------------------------------------------------------------------------
    | CONFIGURATION
    |--------------------------------------------------------------------------
    */

    private static function enabled(): bool
    {
        return (bool) config('app.profiler', false);
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    /**
     * @return array<string, float>
     */
    private static function roundedDurations(): array
    {
        $durations = [];

        foreach (self::$durations as $name => $duration)
        {
            $durations[$name] = round($duration, 2);
        }

        return $durations;
    }

    private static function reset(): void
    {
        self::$active = false;
        self::$requestStart = null;
        self::$starts = [];
        self::$durations = [];
        self::$counters = [];
    }

    private static function elapsedMilliseconds(int|float $start): float
    {
        return (hrtime(true) - $start) / 1_000_000;
    }
}