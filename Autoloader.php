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
        /* Ignore les classes hors namespace App */
        if (!str_starts_with($class, 'App\\'))
        {
            return;
        }

        /* Supprime "App\" du début */
        $relativeClass = substr($class, 4);

        /* Convertit namespace en chemin */
        $relativeClass = str_replace('\\', DIRECTORY_SEPARATOR, $relativeClass);

        /* Construit le chemin du fichier */
        $file = ROOT . DIRECTORY_SEPARATOR . $relativeClass . '.php';

        /* Charge le fichier si existant */
        if (is_file($file))
        {
            require $file;
        }
    }
}