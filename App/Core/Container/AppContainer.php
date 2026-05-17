<?php

declare(strict_types=1);

namespace App\Core\Container;

use RuntimeException;

final class AppContainer
{
    private static ?Container $container = null;

    public static function set(Container $container): void
    {
        self::$container = $container;
    }

    public static function get(): Container
    {
        if (self::$container === null)
        {
            throw new RuntimeException('Container non initialisé.');
        }

        return self::$container;
    }
}