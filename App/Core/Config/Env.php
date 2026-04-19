<?php

declare(strict_types=1);

namespace App\Core\Config;

final class Env
{
    /**
     * Cache mémoire des variables d'environnement déjà lues.
     *
     * @var array<string, mixed>
     */
    private static array $items = [];

    /**
     * Retourne une variable d'environnement.
     *
     * @param mixed $default
     */
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

        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);

        if ($value === false || $value === null)
        {
            self::$items[$key] = $default;
            return self::$items[$key];
        }

        if (is_string($value))
        {
            $value = self::cast(trim($value));
        }

        self::$items[$key] = $value;

        return self::$items[$key];
    }

    /**
     * Vérifie si une variable d'environnement existe.
     */
    public static function has(string $key): bool
    {
        $key = trim($key);

        if ($key === '')
        {
            return false;
        }

        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);

        return $value !== false && $value !== null;
    }

    /**
     * Vide le cache mémoire local.
     */
    public static function clear(): void
    {
        self::$items = [];
    }

    /**
     * Convertit une valeur texte en type PHP cohérent.
     */
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