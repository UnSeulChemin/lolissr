<?php

declare(strict_types=1);

namespace App\Core;

final class Config
{
    /**
     * Cache local des fichiers de configuration chargés.
     *
     * @var array<string, array<string, mixed>>
     */
    private static array $items = [];

    /**
     * Retourne une valeur de configuration.
     *
     * Exemples :
     * - Config::get('app.name')
     * - Config::get('database.host')
     * - Config::get('upload.allowed_extensions', [])
     *
     * @param mixed $default
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $segments = explode('.', $key);
        $file = array_shift($segments);

        if ($file === null || $file === '')
        {
            return $default;
        }

        $config = self::load($file);

        if ($segments === [])
        {
            return $config !== [] ? $config : $default;
        }

        $value = $config;

        foreach ($segments as $segment)
        {
            if (!is_array($value) || !array_key_exists($segment, $value))
            {
                return $default;
            }

            $value = $value[$segment];
        }

        return $value;
    }

    /**
     * Vérifie si une clé de configuration existe.
     */
    public static function has(string $key): bool
    {
        $segments = explode('.', $key);
        $file = array_shift($segments);

        if ($file === null || $file === '')
        {
            return false;
        }

        $config = self::load($file);

        if ($segments === [])
        {
            return $config !== [];
        }

        $value = $config;

        foreach ($segments as $segment)
        {
            if (!is_array($value) || !array_key_exists($segment, $value))
            {
                return false;
            }

            $value = $value[$segment];
        }

        return true;
    }

    /**
     * Vide le cache local de configuration.
     */
    public static function clear(): void
    {
        self::$items = [];
    }

    /**
     * Charge un fichier de configuration.
     *
     * @return array<string, mixed>
     */
    private static function load(string $file): array
    {
        if (isset(self::$items[$file]))
        {
            return self::$items[$file];
        }

        $path = ROOT . '/Config/' . $file . '.php';

        if (!is_file($path))
        {
            self::$items[$file] = [];
            return self::$items[$file];
        }

        $config = require $path;

        if (!is_array($config))
        {
            $config = [];
        }

        self::$items[$file] = $config;

        return self::$items[$file];
    }
}