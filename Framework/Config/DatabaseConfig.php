<?php

declare(strict_types=1);

namespace Framework\Config;

final class DatabaseConfig
{
    private function __construct()
    {
    }

    // =========================================
    // CONFIGURATION
    // =========================================

    public static function host(): string
    {
        return self::getString('database.host', 'localhost');
    }

    public static function port(): int
    {
        return min(
            65535,
            max(1, (int) config('database.port', 3306))
        );
    }

    public static function name(): string
    {
        return self::getString('database.name');
    }

    public static function user(): string
    {
        return self::getString('database.user');
    }

    public static function pass(): string
    {
        return self::getString('database.pass');
    }

    public static function charset(): string
    {
        return self::getString('database.charset', 'utf8mb4');
    }

    // =========================================
    // RÉSOLUTION
    // =========================================

    private static function getString(string $key, string $default = ''): string
    {
        return trim((string) config($key, $default));
    }
}