<?php

declare(strict_types=1);

namespace App\Core\Config;

final class DatabaseConfig
{
    public static function host(): string
    {
        return (string) \config('database.host', 'localhost');
    }

    public static function name(): string
    {
        return (string) \config('database.name', '');
    }

    public static function user(): string
    {
        return (string) \config('database.user', '');
    }

    public static function pass(): string
    {
        return (string) \config('database.pass', '');
    }

    public static function charset(): string
    {
        return (string) \config('database.charset', 'utf8mb4');
    }
}