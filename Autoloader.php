<?php

declare(strict_types=1);

namespace App;

class Autoloader
{
    /**
     * enregistre l'autoloader
     */
    public static function register(): void
    {
        spl_autoload_register([self::class, 'autoload']);
    }

    /**
     * charge automatiquement les classes du namespace App
     */
    public static function autoload(string $class): void
    {
        if (!str_starts_with($class, 'App\\'))
        {
            return;
        }

        $relativeClass = substr($class, 4);
        $relativeClass = str_replace('\\', DIRECTORY_SEPARATOR, $relativeClass);

        $file = ROOT . DIRECTORY_SEPARATOR . $relativeClass . '.php';

        if (is_file($file))
        {
            require_once $file;
        }
    }
}