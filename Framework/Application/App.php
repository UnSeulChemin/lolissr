<?php

declare(strict_types=1);

namespace Framework\Application;

final class App
{
    private const ENV_LOCAL = 'local';
    private const ENV_TESTING = 'testing';
    private const ENV_PRODUCTION = 'production';

    private function __construct()
    {
    }

    // =========================================
    // CONFIGURATION
    // =========================================

    public static function baseUri(): string
    {
        $baseUri = trim((string) config('app.base_uri', '/'));

        if ($baseUri === '' || $baseUri === '/')
        {
            return '/';
        }

        return '/' . trim($baseUri, '/') . '/';
    }

    public static function timezone(): string
    {
        return (string) config('app.timezone', 'Europe/Paris');
    }

    public static function siteName(): string
    {
        return (string) config('app.name', 'Site');
    }

    public static function pagination(): int
    {
        return max(1, (int) config('app.pagination', 8));
    }

    public static function env(): string
    {
        return mb_strtolower(trim((string) config('app.env', self::ENV_LOCAL)));
    }

    public static function debug(): bool
    {
        return (bool) config('app.debug', false);
    }

    // =========================================
    // ENVIRONNEMENT
    // =========================================

    public static function isLocal(): bool
    {
        return self::env() === self::ENV_LOCAL;
    }

    public static function isTesting(): bool
    {
        return self::env() === self::ENV_TESTING;
    }

    public static function isProduction(): bool
    {
        return self::env() === self::ENV_PRODUCTION;
    }
}