<?php

declare(strict_types=1);

namespace Framework\Support;

use RuntimeException;

final class Session
{
    private static bool $started = false;

    private const FLASH_KEY = '_flash';

    private static function ensureStarted(): void
    {
        if (
            self::$started
            || session_status() === PHP_SESSION_ACTIVE
        ) {
            self::$started = true;

            return;
        }

        $directory = base_path(
            'storage/sessions',
        );

        if (
            ! is_dir($directory)
            && ! mkdir(
                $directory,
                0755,
                true,
            )
            && ! is_dir($directory)
        ) {
            throw new RuntimeException(
                'Impossible de créer le dossier de session.',
            );
        }

        session_save_path(
            $directory,
        );

        session_name(
            (string) env(
                'SESSION_NAME',
                'LOLISSR_SESSION',
            ),
        );

        $secure = self::isHttps();

        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.use_trans_sid', '0');
        ini_set('session.cookie_httponly', '1');
        ini_set(
            'session.cookie_secure',
            $secure ? '1' : '0',
        );

        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'domain' => '',
            'secure' => $secure,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        session_start([
            'use_strict_mode' => true,
            'use_only_cookies' => true,
            'use_trans_sid' => false,
            'cookie_httponly' => true,
            'cookie_secure' => $secure,
            'cookie_samesite' => 'Lax',
        ]);

        file_put_contents(
    base_path('storage/logs/session.log'),
    sprintf(
        "[%s] uri=%s session_id=%s cookie=%s\n",
        date('Y-m-d H:i:s'),
        $_SERVER['REQUEST_URI'] ?? '',
        session_id(),
        $_COOKIE['LOLISSR_SESSION'] ?? 'none',
    ),
    FILE_APPEND,
);

        self::$started = true;
    }

    private static function isHttps(): bool
    {
        $https = $_SERVER['HTTPS'] ?? null;

        return (
            is_string($https)
            && $https !== ''
            && strtolower($https) !== 'off'
        )
        || (int) ($_SERVER['SERVER_PORT'] ?? 0) === 443
        || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';
    }

    public static function set(
        string $key,
        mixed $value,
    ): void {

        self::ensureStarted();

        $_SESSION[$key] = $value;
    }

    public static function get(
        string $key,
        mixed $default = null,
    ): mixed {

        self::ensureStarted();

        return $_SESSION[$key]
            ?? $default;
    }

    public static function has(
        string $key,
    ): bool {

        self::ensureStarted();

        return array_key_exists(
            $key,
            $_SESSION,
        );
    }

    public static function remove(
        string $key,
    ): void {

        self::ensureStarted();

        unset($_SESSION[$key]);
    }

    /**
     * @param list<string> $keys
     */
    public static function forget(
        array $keys,
    ): void {

        self::ensureStarted();

        foreach ($keys as $key) {

            unset($_SESSION[$key]);
        }
    }

    public static function pull(
        string $key,
        mixed $default = null,
    ): mixed {

        self::ensureStarted();

        $value = $_SESSION[$key]
            ?? $default;

        unset($_SESSION[$key]);

        return $value;
    }

    public static function flash(
        string $key,
        mixed $value,
    ): void {

        self::ensureStarted();

        $_SESSION[self::FLASH_KEY][$key] = $value;
    }

    /**
     * @return array<string, mixed>
     */
    public static function flashes(): array
    {
        self::ensureStarted();

        $flash =
            $_SESSION[self::FLASH_KEY]
            ?? [];

        unset(
            $_SESSION[self::FLASH_KEY],
        );

        return $flash;
    }

    public static function regenerate(): void
    {
        self::ensureStarted();

        session_regenerate_id(
            true,
        );
    }

    public static function destroy(): void
    {
        self::ensureStarted();

        $_SESSION = [];

        if (ini_get('session.use_cookies')) {

            $params =
                session_get_cookie_params();

            setcookie(
                session_name(),
                '',
                [
                    'expires' => time() - 42000,
                    'path' => $params['path'],
                    'domain' => $params['domain'],
                    'secure' => $params['secure'],
                    'httponly' => $params['httponly'],
                    'samesite' => $params['samesite']
                        ?? 'Lax',
                ],
            );
        }

        session_destroy();

        self::$started = false;
    }

    public static function start(): void
    {
        self::ensureStarted();
    }
}