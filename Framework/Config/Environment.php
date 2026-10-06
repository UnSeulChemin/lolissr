<?php

declare(strict_types=1);

namespace Framework\Config;

use RuntimeException;

final class Environment
{
    /**
     * @var array<string, mixed>
     */
    private static array $items = [];

    /**
     * @var array<string, array{process: string|false, env?: mixed, server?: mixed}>
     */
    private static array $managedKeys = [];

    /** @var array<string, true> */
    private static array $accessedKeys = [];

    private function __construct()
    {
    }

    // =================================================
    // ENVIRONNEMENT
    // =================================================

    public static function load(string $path): void
    {
        self::clear();

        if (! is_file($path))
        {
            return;
        }

        $lines = @file($path, FILE_IGNORE_NEW_LINES);

        if ($lines === false)
        {
            throw new RuntimeException("Unable to read environment file: {$path}");
        }

        foreach ($lines as $lineNumber => $line)
        {
            self::parseLine($line, $lineNumber + 1);
        }
    }

    public static function set(string $key, mixed $value): void
    {
        $key = self::validateKey($key);
        $environmentValue = $value === null ? null : self::stringify($value);

        if (! isset(self::$managedKeys[$key]))
        {
            $original = ['process' => getenv($key)];
            if (array_key_exists($key, $_ENV)) $original['env'] = $_ENV[$key];
            if (array_key_exists($key, $_SERVER)) $original['server'] = $_SERVER[$key];
            self::$managedKeys[$key] = $original;
        }

        if ($environmentValue === null)
        {
            self::$items[$key] = null;

            self::removeEnvironmentValue($key);

            return;
        }

        self::$items[$key] = $value;

        $_ENV[$key] = $environmentValue;
        $_SERVER[$key] = $environmentValue;

        if (! putenv("{$key}={$environmentValue}"))
        {
            throw new RuntimeException("Unable to set environment variable: {$key}");
        }
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $key = trim($key);

        if ($key === '')
        {
            return $default;
        }

        self::$accessedKeys[$key] = true;

        if (array_key_exists($key, self::$items))
        {
            return self::$items[$key];
        }

        $value = $_ENV[$key] ?? $_SERVER[$key] ?? null;

        if ($value === null)
        {
            $environmentValue = getenv($key);
            $value = $environmentValue !== false ? $environmentValue : null;
        }

        if ($value === null)
        {
            return $default;
        }

        if (is_string($value))
        {
            $value = self::cast($value);
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

        if (! is_scalar($value) && $value !== null)
        {
            return $default;
        }

        $result = filter_var($value, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);

        return $result ?? $default;
    }

    public static function int(string $key, int $default = 0): int
    {
        $value = self::get($key, $default);

        if (! is_int($value) && ! is_string($value))
        {
            return $default;
        }

        $result = filter_var($value, FILTER_VALIDATE_INT);

        return $result !== false ? $result : $default;
    }

    public static function has(string $key): bool
    {
        $key = trim($key);

        if ($key === '')
        {
            return false;
        }

        self::$accessedKeys[$key] = true;

        return array_key_exists($key, self::$items)
            || array_key_exists($key, $_ENV)
            || array_key_exists($key, $_SERVER)
            || getenv($key) !== false;
    }

    public static function clear(): void
    {
        foreach (self::$managedKeys as $key => $original)
        {
            unset($_ENV[$key], $_SERVER[$key]);
            if (array_key_exists('env', $original)) $_ENV[$key] = $original['env'];
            if (array_key_exists('server', $original)) $_SERVER[$key] = $original['server'];
            putenv($original['process'] === false ? $key : $key . '=' . $original['process']);
        }

        self::$items = [];
        self::$managedKeys = [];
        self::$accessedKeys = [];
    }

    /** @return list<string> */
    public static function accessedKeys(): array
    {
        return array_keys(self::$accessedKeys);
    }

    // =================================================
    // CHARGEMENT
    // =================================================

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

        try
        {
            self::set($name, self::normalizeValue($value));
        }
        catch (RuntimeException $exception)
        {
            throw new RuntimeException(
                "Invalid environment declaration at line {$lineNumber}: {$exception->getMessage()}",
                previous: $exception
            );
        }
    }

    private static function normalizeValue(string $value): mixed
    {
        $value = trim($value);
        $length = strlen($value);

        if ($length === 0)
        {
            return $value;
        }

        $firstCharacter = $value[0];
        if ($firstCharacter === '"' || $firstCharacter === "'")
        {
            if ($length < 2 || $value[$length - 1] !== $firstCharacter)
            {
                throw new RuntimeException('Unclosed quoted environment value.');
            }
            return substr($value, 1, -1);
        }

        return self::cast($value);
    }

    // =================================================
    // VALIDATION
    // =================================================

    private static function validateKey(string $key): string
    {
        $key = trim($key);

        if ($key === '')
        {
            throw new RuntimeException('Environment variable name cannot be empty.');
        }

        if (preg_match('/^[A-Z][A-Z0-9_]*$/', $key) !== 1)
        {
            throw new RuntimeException("Invalid environment variable name: {$key}");
        }

        return $key;
    }

    // =================================================
    // CONVERSION
    // =================================================

    private static function cast(string $value): mixed
    {
        return match (strtolower($value))
        {
            'true', '(true)' => true,
            'false', '(false)' => false,
            'null', '(null)' => null,
            'empty', '(empty)' => '',
            default => $value
        };
    }

    private static function stringify(mixed $value): string
    {
        return match (true)
        {
            $value === true => 'true',
            $value === false => 'false',
            is_scalar($value) => (string) $value,
            default => throw new RuntimeException('Environment values must be scalar or null.')
        };
    }

    // =================================================
    // NETTOYAGE
    // =================================================

    private static function removeEnvironmentValue(string $key): void
    {
        unset($_ENV[$key], $_SERVER[$key]);

        putenv($key);
    }
}
