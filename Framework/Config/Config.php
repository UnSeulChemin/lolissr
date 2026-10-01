<?php

declare(strict_types=1);

namespace Framework\Config;

use RuntimeException;

final class Config
{
    /**
     * @var array<string, array<string, mixed>|null>
     */
    private static array $items = [];

    /** @var array<string, array{value?: mixed}> Missing keys keep their caller's default. */
    private static array $resolved = [];

    private function __construct()
    {
    }

    // =========================================
    // CONFIGURATION
    // =========================================

    public static function get(string $key, mixed $default = null): mixed
    {
        $key = trim($key);
        if (! isset(self::$resolved[$key]))
        {
            self::$resolved[$key] = self::resolve($key);
        }

        $resolved = self::$resolved[$key];
        return array_key_exists('value', $resolved) ? $resolved['value'] : $default;
    }

    public static function clear(): void
    {
        self::$items = [];
        self::$resolved = [];
    }

    /** @param array<string, array<string, mixed>> $items */
    public static function prime(array $items): void
    {
        self::$items = $items;
        self::$resolved = [];
    }

    // =========================================
    // RÉSOLUTION
    // =========================================

    /**
     * @return array{value?: mixed}
     */
    private static function resolve(string $key): array
    {
        $segments = array_values(array_filter(
            explode('.', $key),
            static fn (string $segment): bool => $segment !== ''
        ));

        if ($segments === [])
        {
            return [];
        }

        $file = array_shift($segments);

        if (! array_key_exists($file, self::$items))
        {
            self::$items[$file] = self::loadFile($file);
        }
        $value = self::$items[$file];
        if ($value === null) return [];

        foreach ($segments as $segment)
        {
            if (! is_array($value) || ! array_key_exists($segment, $value))
            {
                return [];
            }

            $value = $value[$segment];
        }

        return ['value' => $value];
    }

    // =========================================
    // CHARGEMENT
    // =========================================

    /**
     * @return array<string, mixed>|null
     */
    private static function loadFile(string $file): ?array
    {
        $path = base_path('Config/' . $file . '.php');

        if (! is_file($path))
        {
            return null;
        }

        $config = require $path;

        if (! is_array($config))
        {
            throw new RuntimeException('Configuration must return an array: ' . $file);
        }

        return $config;
    }
}
