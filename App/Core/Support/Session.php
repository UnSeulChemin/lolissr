<?php

declare(strict_types=1);

namespace App\Core\Support;

final class Session
{
    /**
     * Démarre la session si nécessaire.
     */
    private static function ensureStarted(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE)
        {
            return;
        }

        $directory = ROOT . '/storage/sessions';

        if (
            !is_dir($directory)
            && !mkdir($directory, 0755, true)
            && !is_dir($directory)
        ) {
            throw new \RuntimeException(
                'Impossible de créer le dossier de session.'
            );
        }

        session_save_path($directory);

        session_name(
            (string) env(
                'SESSION_NAME',
                'LOLISSR_SESSION'
            )
        );

        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'domain' => '',
            'secure' => !empty($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        session_start();
    }

    /**
     * Enregistre une valeur.
     */
    public static function set(
        string $key,
        mixed $value
    ): void {
        self::ensureStarted();

        $_SESSION[$key] = $value;
    }

    /**
     * Récupère une valeur.
     */
    public static function get(
        string $key,
        mixed $default = null
    ): mixed {
        self::ensureStarted();

        return $_SESSION[$key]
            ?? $default;
    }

    /**
     * Vérifie l'existence d'une clé.
     */
    public static function has(
        string $key
    ): bool {
        self::ensureStarted();

        return array_key_exists(
            $key,
            $_SESSION
        );
    }

    /**
     * Supprime une clé.
     */
    public static function remove(
        string $key
    ): void {
        self::forget([$key]);
    }

    /**
     * Supprime plusieurs clés.
     */
    public static function forget(
        array $keys
    ): void {
        self::ensureStarted();

        foreach ($keys as $key)
        {
            unset($_SESSION[$key]);
        }
    }

    /**
     * Récupère puis supprime.
     */
    public static function pull(
        string $key,
        mixed $default = null
    ): mixed {
        self::ensureStarted();

        $value = $_SESSION[$key]
            ?? $default;

        unset($_SESSION[$key]);

        return $value;
    }

    /**
     * Flash message.
     */
    public static function flash(
        string $key,
        mixed $value
    ): void {
        self::set(
            $key,
            $value
        );
    }

    /**
     * Regénère l'ID de session.
     */
    public static function regenerate(): void
    {
        self::ensureStarted();

        session_regenerate_id(true);
    }

    /**
     * Détruit complètement la session.
     */
    public static function destroy(): void
    {
        self::ensureStarted();

        $_SESSION = [];

        if (ini_get('session.use_cookies'))
        {
            $params = session_get_cookie_params();

            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
    }
}