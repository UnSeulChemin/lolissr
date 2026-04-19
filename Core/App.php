<?php

declare(strict_types=1);

namespace App\Core;

final class App
{
    public static function basePath(): string
    {
        $basePath = trim((string) Config::get('app.base_path', '/'));

        if ($basePath === '' || $basePath === '/')
        {
            return '/';
        }

        return '/' . trim($basePath, '/') . '/';
    }

    public static function siteName(): string
    {
        return (string) Config::get('app.name', 'Site');
    }

    public static function pagination(): int
    {
        return max(1, (int) Config::get('app.pagination', 8));
    }

    public static function env(): string
    {
        return (string) Config::get('app.env', 'local');
    }

    public static function debug(): bool
    {
        return (bool) Config::get('app.debug', false);
    }
}