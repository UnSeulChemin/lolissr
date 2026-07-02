<?php

declare(strict_types=1);

namespace Framework\Config;

final class Config
{
    /**
     * @var array<string, array<string, mixed>>
     */
    private static array $items = [];

    // =========================================
    // CONFIGURATION
    // =========================================

    public static function get(string $key, mixed $default = null): mixed
    {
        $resolved = self::resolve($key);

        if ($resolved === null)
        {
            return $default;
        }

        [$config, $segments] = $resolved;

        if ($segments === [])
        {
            return $config;
        }

        return self::arrayGet($config, $segments, $default);
    }

    public static function has(string $key): bool
    {
        $resolved = self::resolve($key);

        if ($resolved === null)
        {
            return false;
        }

        [$config, $segments] = $resolved;

        if ($segments === [])
        {
            return $config !== [];
        }

        return self::arrayHas($config, $segments);
    }

    public static function clear(): void
    {
        self::$items = [];
    }

    // =========================================
    // RÉSOLUTION
    // =========================================

    /**
     * @return array{
     *     0: array<string, mixed>,
     *     1: list<string>
     * }|null
     */
    private static function resolve(string $key): ?array
    {
        $segments = self::segments($key);

        if ($segments === [])
        {
            return null;
        }

        $file = array_shift($segments);

        if ($file === '')
        {
            return null;
        }

        return [self::load($file), $segments];
    }

    /**
     * @return list<string>
     */
    private static function segments(string $key): array
    {
        $key = trim($key);

        if ($key === '')
        {
            return [];
        }

        return array_values(array_filter(explode('.', $key), static fn (string $segment): bool => $segment !== ''));
    }

    /**
     * @param array<string, mixed> $items
     * @param list<string> $segments
     */
    private static function arrayGet(array $items, array $segments, mixed $default = null): mixed
    {
        $value = $items;

        foreach ($segments as $segment)
        {
            if (! is_array($value) || ! array_key_exists($segment, $value))
            {
                return $default;
            }

            $value = $value[$segment];
        }

        return $value;
    }

    /**
     * @param array<string, mixed> $items
     * @param list<string> $segments
     */
    private static function arrayHas(array $items, array $segments): bool
    {
        $value = $items;

        foreach ($segments as $segment)
        {
            if (! is_array($value) || ! array_key_exists($segment, $value))
            {
                return false;
            }

            $value = $value[$segment];
        }

        return true;
    }

    // =========================================
    // CHARGEMENT
    // =========================================

    /**
     * @return array<string, mixed>
     */
    private static function load(string $file): array
    {
        return self::$items[$file] ??= self::loadFile($file);
    }

    /**
     * @return array<string, mixed>
     */
    private static function loadFile(string $file): array
    {
        $path = base_path('Config/' . $file . '.php');

        if (! is_file($path))
        {
            return [];
        }

        $config = require $path;

        return is_array($config) ? $config : [];
    }
}
