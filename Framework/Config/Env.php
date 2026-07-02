<?php

declare(strict_types=1);

namespace Framework\Config;

final class Env
{
    /**
     * @var array<string, mixed>
     */
    private static array $items = [];

    // =========================================
    // ENVIRONNEMENT
    // =========================================

    public static function get(string $key, mixed $default = null): mixed
    {
        $key = trim($key);

        if ($key === '')
        {
            return $default;
        }

        if (array_key_exists($key, self::$items))
        {
            return self::$items[$key];
        }

        $value = $_ENV[$key] ?? $_SERVER[$key] ?? null;

        if ($value === null)
        {
            $env = getenv($key);

            $value = $env !== false ? $env : null;
        }

        if ($value === null)
        {
            self::$items[$key] = $default;

            return $default;
        }

        if (is_string($value))
        {
            $value = self::cast(trim($value));
        }

        self::$items[$key] = $value;

        return $value;
    }

    public static function bool(string $key, bool $default = false): bool
    {
        $value = self::get($key, $default);

        if (is_bool($value))
        {
            return $value;
        }

        $result = filter_var($value, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);

        return $result ?? $default;
    }

    public static function int(string $key, int $default = 0): int
    {
        $value = self::get($key, $default);

        return filter_var($value, FILTER_VALIDATE_INT) !== false
            ? (int) $value
            : $default;
    }

    public static function has(string $key): bool
    {
        $key = trim($key);

        if ($key === '')
        {
            return false;
        }

        return array_key_exists($key, $_ENV)
            || array_key_exists($key, $_SERVER)
            || getenv($key) !== false;
    }

    public static function clear(): void
    {
        self::$items = [];
    }

    // =========================================
    // CONVERSION
    // =========================================

    private static function cast(string $value): mixed
    {
        return match (strtolower($value))
        {
            'true', '(true)' => true,

            'false', '(false)' => false,

            'null', '(null)' => null,

            'empty', '(empty)' => '',

            default => $value,
        };
    }
}
