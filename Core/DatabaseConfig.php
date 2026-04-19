<?php

declare(strict_types=1);

namespace App\Core;

final class DatabaseConfig
{
    public static function host(): string
    {
        return (string) Config::get('database.host', 'localhost');
    }

    public static function name(): string
    {
        return (string) Config::get('database.name', '');
    }

    public static function user(): string
    {
        return (string) Config::get('database.user', '');
    }

    public static function pass(): string
    {
        return (string) Config::get('database.pass', '');
    }

    public static function charset(): string
    {
        return (string) Config::get('database.charset', 'utf8mb4');
    }
}