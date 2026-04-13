<?php

namespace App\Core;

class Session
{
    /**
     * Enregistre une valeur en session.
     */
    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Récupère une valeur de session.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Vérifie si une clé de session existe.
     */
    public static function has(string $key): bool
    {
        return array_key_exists($key, $_SESSION);
    }

    /**
     * Supprime une clé de session.
     */
    public static function remove(string $key): void
    {
        self::forget([$key]);
    }

    /**
     * Supprime plusieurs clés de session.
     */
    public static function forget(array $keys): void
    {
        foreach ($keys as $key)
        {
            unset($_SESSION[$key]);
        }
    }

    /**
     * Récupère une valeur puis la supprime.
     */
    public static function pull(string $key, mixed $default = null): mixed
    {
        $value = $_SESSION[$key] ?? $default;
        unset($_SESSION[$key]);

        return $value;
    }
}