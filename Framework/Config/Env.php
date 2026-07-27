<?php

declare(strict_types=1);

namespace Framework\Config;

use RuntimeException;

final class Env
{
    /**
     * @var array<string, mixed>
     */
    private static array $items = [];

    // =========================================
    // ENVIRONNEMENT
    // =========================================

    public static function load(string $path): void
    {
        self::clear();

        if (! is_file($path))
        {
            return;
        }

        $lines = file(
            $path,
            FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES
        );

        if ($lines === false)
        {
            throw new RuntimeException(
                "Unable to read environment file: {$path}"
            );
        }

        foreach ($lines as $lineNumber => $line)
        {
            self::parseLine($line, $lineNumber + 1);
        }
    }

    public static function set(string $key, mixed $value): void
    {
        $key = trim($key);

        if ($key === '')
        {
            throw new RuntimeException(
                'Environment variable name cannot be empty.'
            );
        }

        if (preg_match('/^[A-Z][A-Z0-9_]*$/', $key) !== 1)
        {
            throw new RuntimeException(
                "Invalid environment variable name: {$key}"
            );
        }

        self::$items[$key] = $value;

        if ($value === null)
        {
            unset($_ENV[$key], $_SERVER[$key]);

            putenv($key);

            return;
        }

        $environmentValue = self::stringify($value);

        $_ENV[$key] = $environmentValue;
        $_SERVER[$key] = $environmentValue;

        putenv("{$key}={$environmentValue}");
    }

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
            $environmentValue = getenv($key);

            $value = $environmentValue !== false
                ? $environmentValue
                : null;
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

        $result = filter_var(
            $value,
            FILTER_VALIDATE_BOOL,
            FILTER_NULL_ON_FAILURE
        );

        return $result ?? $default;
    }

    public static function int(string $key, int $default = 0): int
    {
        $value = self::get($key, $default);
        $result = filter_var($value, FILTER_VALIDATE_INT);

        return $result !== false
            ? $result
            : $default;
    }

    public static function has(string $key): bool
    {
        $key = trim($key);

        if ($key === '')
        {
            return false;
        }

        return array_key_exists($key, self::$items)
            || array_key_exists($key, $_ENV)
            || array_key_exists($key, $_SERVER)
            || getenv($key) !== false;
    }

    public static function clear(): void
    {
        self::$items = [];
    }

    // =========================================
    // CHARGEMENT
    // =========================================

    private static function parseLine(string $line, int $lineNumber): void
    {
        $line = trim($line);

        if ($line === '' || str_starts_with($line, '#'))
        {
            return;
        }

        if (! str_contains($line, '='))
        {
            throw new RuntimeException(
                "Invalid environment declaration at line {$lineNumber}."
            );
        }

        [$name, $value] = explode('=', $line, 2);

        $name = trim($name);

        if ($name === '')
        {
            throw new RuntimeException(
                "Missing environment variable name at line {$lineNumber}."
            );
        }

        if (preg_match('/^[A-Z][A-Z0-9_]*$/', $name) !== 1)
        {
            throw new RuntimeException(
                "Invalid environment variable name at line {$lineNumber}: {$name}"
            );
        }

        self::set(
            $name,
            self::normalizeValue($value)
        );
    }

    private static function normalizeValue(string $value): string
    {
        $value = trim($value);

        if (strlen($value) < 2)
        {
            return $value;
        }

        $firstCharacter = $value[0];
        $lastCharacter = $value[strlen($value) - 1];

        if (
            ($firstCharacter === '"' && $lastCharacter === '"')
            || ($firstCharacter === "'" && $lastCharacter === "'")
        )
        {
            return substr($value, 1, -1);
        }

        return $value;
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

    private static function stringify(mixed $value): string
    {
        return match (true)
        {
            $value === true => 'true',
            $value === false => 'false',
            is_scalar($value) => (string) $value,
            default => throw new RuntimeException(
                'Environment values must be scalar or null.'
            ),
        };
    }
}