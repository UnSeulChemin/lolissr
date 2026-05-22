<?php

declare(strict_types=1);

namespace Framework\Config;

final class DatabaseConfig
{
    public static function host(): string
    {
        return self::string(
            'database.host',
            'localhost',
        );
    }

    public static function port(): int
    {
        return max(
            1,
            (int) config(
                'database.port',
                3306,
            ),
        );
    }

    public static function name(): string
    {
        return self::string(
            'database.name',
        );
    }

    public static function user(): string
    {
        return self::string(
            'database.user',
        );
    }

    public static function pass(): string
    {
        return self::string(
            'database.pass',
        );
    }

    public static function charset(): string
    {
        return self::string(
            'database.charset',
            'utf8mb4',
        );
    }

    private static function string(
        string $key,
        string $default = '',
    ): string {
        return trim(
            (string) config(
                $key,
                $default,
            ),
        );
    }
}