<?php

declare(strict_types=1);

namespace Framework\Container;

use RuntimeException;

final class AppContainer
{
    private static ?Container $container = null;

    // =========================================
    // CONTAINER
    // =========================================

    public static function set(Container $container): void
    {
        self::$container = $container;
    }

    public static function get(): Container
    {
        if (self::$container !== null)
        {
            return self::$container;
        }

        throw new RuntimeException('Container non initialisé.');
    }

    public static function has(): bool
    {
        return self::$container !== null;
    }

    public static function clear(): void
    {
        self::$container = null;
    }
}
