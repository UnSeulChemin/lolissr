<?php

declare(strict_types=1);

namespace Framework\Application;

final class App
{
    public static function baseUri(): string
    {
        $baseUri = trim(
            (string) config(
                'app.base_uri',
                '/',
            ),
        );

        if (
            $baseUri === ''
            || $baseUri === '/'
        ) {
            return '/';
        }

        return '/'
            . trim($baseUri, '/')
            . '/';
    }

    public static function timezone(): string
    {
        return (string) config(
            'app.timezone',
            'Europe/Paris',
        );
    }

    public static function siteName(): string
    {
        return (string) config(
            'app.name',
            'Site',
        );
    }

    public static function pagination(): int
    {
        return max(
            1,
            (int) config(
                'app.pagination',
                8,
            ),
        );
    }

    public static function env(): string
    {
        return strtolower(
            (string) config(
                'app.env',
                'local',
            ),
        );
    }

    public static function debug(): bool
    {
        return (bool) config(
            'app.debug',
            false,
        );
    }

    public static function isTesting(): bool
    {
        return self::env() === 'testing';
    }

    public static function isProduction(): bool
    {
        return self::env() === 'production';
    }

    public static function isLocal(): bool
    {
        return self::env() === 'local';
    }

    public static function isReadOnly(): bool
    {
        return self::isTesting();
    }
}
