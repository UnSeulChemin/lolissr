<?php

declare(strict_types=1);

namespace Framework\Support;

use RuntimeException;

final class Session
{
    private const FLASH_KEY = '_flash';
    private const DEFAULT_SESSION_NAME = 'APP_SESSION';

    private static bool $started = false;

    private static ?string $directory = null;

    private function __construct()
    {
    }

    // =========================================
    // SESSION
    // =========================================

    public static function start(): void
    {
        self::ensureStarted();
    }

    public static function set(string $key, mixed $value): void
    {
        self::ensureStarted();

        $_SESSION[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        self::ensureStarted();

        return array_key_exists($key, $_SESSION)
            ? $_SESSION[$key]
            : $default;
    }

    public static function has(string $key): bool
    {
        self::ensureStarted();

        return array_key_exists($key, $_SESSION);
    }

    public static function remove(string $key): void
    {
        self::ensureStarted();

        unset($_SESSION[$key]);
    }

    /**
     * @param list<string> $keys
     */
    public static function forget(array $keys): void
    {
        self::ensureStarted();

        foreach ($keys as $key)
        {
            unset($_SESSION[$key]);
        }
    }

    public static function pull(string $key, mixed $default = null): mixed
    {
        self::ensureStarted();

        $value = array_key_exists($key, $_SESSION)
            ? $_SESSION[$key]
            : $default;

        unset($_SESSION[$key]);

        return $value;
    }

    // =========================================
    // FLASH
    // =========================================

    public static function flash(string $key, mixed $value): void
    {
        self::ensureStarted();

        $flashes = $_SESSION[self::FLASH_KEY] ?? [];

        if (! is_array($flashes))
        {
            $flashes = [];
        }

        $flashes[$key] = $value;

        $_SESSION[self::FLASH_KEY] = $flashes;
    }

    /**
     * @return array<string, mixed>
     */
    public static function flashes(): array
    {
        self::ensureStarted();

        $flashes = $_SESSION[self::FLASH_KEY] ?? [];

        unset($_SESSION[self::FLASH_KEY]);

        return is_array($flashes) ? $flashes : [];
    }

    // =========================================
    // CYCLE DE VIE
    // =========================================

    public static function regenerate(): void
    {
        self::ensureStarted();

        if (! session_regenerate_id(true))
        {
            throw new RuntimeException(
                'Impossible de régénérer l’identifiant de session.'
            );
        }
    }

    public static function destroy(): void
    {
        self::ensureStarted();

        $_SESSION = [];

        if (ini_get('session.use_cookies') === '1' && ! headers_sent())
        {
            $params = session_get_cookie_params();
            $sessionName = session_name();

            if ($sessionName === '')
            {
                $sessionName = self::DEFAULT_SESSION_NAME;
            }

            setcookie(
                $sessionName,
                '',
                [
                    'expires' => time() - 42000,
                    'path' => $params['path'],
                    'domain' => $params['domain'],
                    'secure' => $params['secure'],
                    'httponly' => $params['httponly'],
                    'samesite' => $params['samesite'] ?? 'Lax',
                ]
            );
        }

        if (session_status() === PHP_SESSION_ACTIVE && ! session_destroy())
        {
            throw new RuntimeException(
                'Impossible de détruire la session.'
            );
        }

        $_SESSION = [];

        self::$started = false;
    }

    // =========================================
    // INITIALISATION
    // =========================================

    private static function ensureStarted(): void
    {
        if (self::$started)
        {
            return;
        }

        if (session_status() === PHP_SESSION_ACTIVE)
        {
            self::$started = true;

            return;
        }

        if (session_status() === PHP_SESSION_DISABLED)
        {
            throw new RuntimeException(
                'Les sessions PHP sont désactivées.'
            );
        }

        if (headers_sent($file, $line))
        {
            throw new RuntimeException(
                "Impossible de démarrer la session : en-têtes déjà envoyés dans {$file}:{$line}."
            );
        }

        $directory = self::directory();

        if (! self::ensureDirectory($directory))
        {
            throw new RuntimeException(
                'Impossible de créer le dossier de session.'
            );
        }

        if (session_save_path($directory) === false)
        {
            throw new RuntimeException(
                'Impossible de configurer le dossier de session.'
            );
        }

        $sessionName = self::sessionName();

        if (session_name($sessionName) === false)
        {
            throw new RuntimeException(
                'Impossible de configurer le nom de session.'
            );
        }

        $secure = self::isHttps();

        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.use_trans_sid', '0');
        ini_set('session.cookie_httponly', '1');
        ini_set('session.cookie_secure', $secure ? '1' : '0');

        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'domain' => '',
            'secure' => $secure,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        if (! @session_start([
            'use_strict_mode' => true,
            'use_only_cookies' => true,
            'use_trans_sid' => false,
            'cookie_httponly' => true,
            'cookie_secure' => $secure,
            'cookie_samesite' => 'Lax',
        ])) {
            throw new RuntimeException(
                'Impossible de démarrer la session.'
            );
        }

        self::$started = true;
    }

    // =========================================
    // CONFIGURATION
    // =========================================

    private static function sessionName(): string
    {
        $sessionName = trim(
            (string) env('SESSION_NAME', self::DEFAULT_SESSION_NAME)
        );

        if (
            $sessionName === ''
            || preg_match('/^[a-zA-Z0-9_-]+$/', $sessionName) !== 1
        ) {
            throw new RuntimeException(
                "Nom de session invalide : {$sessionName}"
            );
        }

        return $sessionName;
    }

    // =========================================
    // HTTPS
    // =========================================

    private static function isHttps(): bool
    {
        $https = $_SERVER['HTTPS'] ?? null;

        if (is_string($https) && $https !== '' && strtolower($https) !== 'off')
        {
            return true;
        }

        if ((int) ($_SERVER['SERVER_PORT'] ?? 0) === 443)
        {
            return true;
        }

        if (! env_bool('TRUST_PROXY', false))
        {
            return false;
        }

        $forwardedProto = (string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '');
        $forwardedProto = trim(explode(',', $forwardedProto)[0]);

        return strtolower($forwardedProto) === 'https';
    }

    // =========================================
    // DOSSIER
    // =========================================

    private static function directory(): string
    {
        return self::$directory ??= base_path('storage/sessions');
    }

    private static function ensureDirectory(string $directory): bool
    {
        if (is_dir($directory))
        {
            return true;
        }

        if (@mkdir($directory, 0755, true))
        {
            return true;
        }

        return is_dir($directory);
    }
}