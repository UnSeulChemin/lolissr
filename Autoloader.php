<?php

declare(strict_types=1);

namespace App;

class Autoloader
{
    /**
     * Enregistre l'autoloader.
     */
    public static function register(): void
    {
        spl_autoload_register([self::class, 'autoload']);
    }

    /**
     * Charge automatiquement les classes du namespace App.
     */
    public static function autoload(string $class): void
    {
        if (!str_starts_with($class, 'App\\'))
        {
            return;
        }

        // Supprime "App\"
        $relativeClass = substr($class, 4);

        // Convertit namespace en chemin
        $relativeClass = str_replace(
            '\\',
            DIRECTORY_SEPARATOR,
            $relativeClass
        );

        // Chemin vers App/
        $file = ROOT
            . DIRECTORY_SEPARATOR
            . 'App'
            . DIRECTORY_SEPARATOR
            . $relativeClass
            . '.php';

        if (is_file($file))
        {
            require_once $file;
        }
    }
}