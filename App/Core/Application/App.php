<?php

declare(strict_types=1);

namespace App\Core\Application;

use App\Core\Config\Config;

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
        return strtolower((string) Config::get('app.env', 'local'));
    }

    public static function debug(): bool
    {
        return (bool) Config::get('app.debug', false);
    }

    /**
     * Indique si l'application tourne en environnement de test.
     */
    public static function isTesting(): bool
    {
        return self::env() === 'testing';
    }

    /**
     * Indique si les écritures doivent être bloquées.
     */
    public static function isReadOnly(): bool
    {
        return self::isTesting();
    }
}