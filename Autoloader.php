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
     * Charge automatiquement les classes.
     */
    public static function autoload(string $class): void
    {
        $prefixes = [
            'App\\' => 'App',
            'Framework\\' => 'Framework',
        ];

        foreach ($prefixes as $prefix => $baseDir)
        {
            if (!str_starts_with($class, $prefix))
            {
                continue;
            }

            $relativeClass = substr(
                $class,
                strlen($prefix)
            );

            $relativeClass = str_replace(
                '\\',
                DIRECTORY_SEPARATOR,
                $relativeClass
            );

            $file = ROOT
                . DIRECTORY_SEPARATOR
                . $baseDir
                . DIRECTORY_SEPARATOR
                . $relativeClass
                . '.php';

            if (is_file($file))
            {
                require_once $file;
            }

            return;
        }
    }
}