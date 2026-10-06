<?php

declare(strict_types=1);

namespace Framework\Config;

use RuntimeException;

final class Config
{
    private const FILE_PATHS = [
        'app' => 'settings/application.php',
        'cache' => 'settings/cache.php',
        'database' => 'settings/database.php',
        'log' => 'settings/logging.php',
        'session' => 'settings/session.php',
        'upload' => 'settings/uploads.php',
        'manga-releases' => 'settings/manga-releases.php',
        'assets' => 'assets/versions.php',
        'javascript' => 'assets/javascript-manifest.php',
        'styles' => 'assets/page-styles.php'
    ];

    /** @return list<string> */
    public static function names(): array
    {
        $names = [];
        foreach (self::FILE_PATHS as $name => $path)
        {
            if (is_file(base_path('Config/' . $path))) $names[] = $name;
        }
        $files = glob(base_path('Config/*.php'));
        if ($files === false) throw new RuntimeException('Cannot list configuration files.');
        foreach ($files as $file)
        {
            $name = basename($file, '.php');
            if (!isset(self::FILE_PATHS[$name])) $names[] = $name;
        }
        sort($names);
        return $names;
    }

    /**
     * @var array<string, array<string, mixed>|null>
     */
    private static array $items = [];

    /** @var array<string, array{value?: mixed}> Missing keys keep their caller's default. */
    private static array $resolved = [];

    private function __construct()
    {
    }

    // =================================================
    // CONFIGURATION
    // =================================================

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

    // =================================================
    // RÉSOLUTION
    // =================================================

    /**
     * @return array{value?: mixed}
     */
    private static function resolve(string $key): array
    {
        $segments = array_values(array_filter(explode('.', $key), static fn (string $segment): bool => $segment !== ''));

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

    // =================================================
    // CHARGEMENT
    // =================================================

    /**
     * @return array<string, mixed>|null
     */
    private static function loadFile(string $file): ?array
    {
        $path = base_path('Config/' . (self::FILE_PATHS[$file] ?? $file . '.php'));

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
