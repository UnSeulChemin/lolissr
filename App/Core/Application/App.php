<?php

declare(strict_types=1);

namespace App\Core\Application;

final class App
{
    public static function basePath(): string
    {
        $basePath = trim((string) \config('app.base_path', '/'));

        if ($basePath === '' || $basePath === '/')
        {
            return '/';
        }

        return '/' . trim($basePath, '/') . '/';
    }

    public static function siteName(): string
    {
        return (string) \config('app.name', 'Site');
    }

    public static function pagination(): int
    {
        return max(1, (int) \config('app.pagination', 8));
    }

    public static function env(): string
    {
        return strtolower((string) \config('app.env', 'local'));
    }

    public static function debug(): bool
    {
        return (bool) \config('app.debug', false);
    }

    public static function isTesting(): bool
    {
        return self::env() === 'testing';
    }

    public static function isReadOnly(): bool
    {
        return self::isTesting();
    }
}