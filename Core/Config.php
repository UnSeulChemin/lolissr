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
        $segments = self::segments($key);

        if ($segments === [])
        {
            return $default;
        }

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

        return self::arrayGet($config, $segments, $default);
    }

    /**
     * Vérifie si une clé de configuration existe.
     */
    public static function has(string $key): bool
    {
        $segments = self::segments($key);

        if ($segments === [])
        {
            return false;
        }

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

        return self::arrayHas($config, $segments);
    }

    /**
     * Vide le cache local de configuration.
     */
    public static function clear(): void
    {
        self::$items = [];
    }

    /**
     * Retourne les segments d'une clé de configuration.
     *
     * @return string[]
     */
    private static function segments(string $key): array
    {
        $key = trim($key);

        if ($key === '')
        {
            return [];
        }

        $segments = explode('.', $key);

        $segments = array_values(array_filter(
            $segments,
            static fn (string $segment): bool => $segment !== ''
        ));

        return $segments;
    }

    /**
     * Retourne une valeur dans un tableau imbriqué.
     *
     * @param array<string, mixed> $items
     * @param string[] $segments
     * @param mixed $default
     */
    private static function arrayGet(array $items, array $segments, mixed $default = null): mixed
    {
        $value = $items;

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
     * Vérifie l'existence d'une clé dans un tableau imbriqué.
     *
     * @param array<string, mixed> $items
     * @param string[] $segments
     */
    private static function arrayHas(array $items, array $segments): bool
    {
        $value = $items;

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