<?php

namespace App\Core;

class Session
{
    /**
     * Vérifie que la session est démarrée.
     */
    private static function ensureStarted(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE)
        {
            session_start();
        }
    }

    /**
     * Enregistre une valeur en session.
     */
    public static function set(string $key, mixed $value): void
    {
        self::ensureStarted();
        $_SESSION[$key] = $value;
    }

    /**
     * Récupère une valeur de session.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        self::ensureStarted();
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Vérifie si une clé existe.
     */
    public static function has(string $key): bool
    {
        self::ensureStarted();
        return array_key_exists($key, $_SESSION);
    }

    /**
     * Supprime une clé.
     */
    public static function remove(string $key): void
    {
        self::forget([$key]);
    }

    /**
     * Supprime plusieurs clés.
     */
    public static function forget(array $keys): void
    {
        self::ensureStarted();

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
        self::ensureStarted();

        $value = $_SESSION[$key] ?? $default;
        unset($_SESSION[$key]);

        return $value;
    }

    /**
     * Message flash (une seule requête).
     */
    public static function flash(string $key, mixed $value): void
    {
        self::set($key, $value);
    }
}