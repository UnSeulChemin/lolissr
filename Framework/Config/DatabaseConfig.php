<?php

declare(strict_types=1);

namespace Framework\Config;

final class DatabaseConfig
{
    private function __construct()
    {
    }

    // =========================================
    // CONNEXION
    // =========================================

    public static function host(): string
    {
        return self::string('database.host', 'localhost');
    }

    public static function port(): int
    {
        return (int) config('database.port', 3306);
    }

    public static function name(): string
    {
        return self::string('database.name');
    }

    public static function user(): string
    {
        return self::string('database.user');
    }

    public static function pass(): string
    {
        return self::string('database.pass');
    }

    public static function charset(): string
    {
        return self::string('database.charset', 'utf8mb4');
    }

    // =========================================
    // PROFILING
    // =========================================

    public static function slowQueryThreshold(): float
    {
        return max(1.0, (float) config('database.slow_query_threshold', 50));
    }

    // =========================================
    // RÉSOLUTION
    // =========================================

    private static function string(string $key, string $default = ''): string
    {
        return trim((string) config($key, $default));
    }
}