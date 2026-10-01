<?php

declare(strict_types=1);

namespace Framework\Http;

use RuntimeException;

final class Session
{
    private const DEFAULT_SESSION_NAME = 'APP_SESSION';

    private static bool $releaseAfterAccess = false;

    private static ?string $directory = null;

    /** @var array<string, mixed>|null */
    private static ?array $configuration = null;

    /** @var array<string, bool|string> */
    private static array $startOptions = [];

    private function __construct()
    {
    }

    // =========================================
    // SESSION
    // =========================================

    public static function start(): void
    {
        self::ensureStarted();
        self::$releaseAfterAccess = false;
    }

    public static function close(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE && ! session_write_close())
        {
            throw new RuntimeException('Impossible de sauvegarder la session.');
        }
        self::$releaseAfterAccess = true;
    }

    /**
     * @template T
     * @param callable(): T $callback
     * @return T
     */
    public static function withLock(callable $callback): mixed
    {
        $wasActive = session_status() === PHP_SESSION_ACTIVE;
        $releaseAfterAccess = self::$releaseAfterAccess;
        self::start();

        try
        {
            return $callback();
        }
        finally
        {
            if (! $wasActive)
            {
                self::close();
            }
            else
            {
                self::$releaseAfterAccess = $releaseAfterAccess;
            }
        }
    }

    public static function set(string $key, mixed $value): void
    {
        self::ensureStarted();

        $_SESSION[$key] = $value;
        self::releaseIfNeeded();
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        self::ensureStarted();

        $value = array_key_exists($key, $_SESSION)
            ? $_SESSION[$key]
            : $default;
        self::releaseIfNeeded();

        return $value;
    }

    public static function has(string $key): bool
    {
        self::ensureStarted();

        $exists = array_key_exists($key, $_SESSION);
        self::releaseIfNeeded();

        return $exists;
    }

    public static function remove(string $key): void
    {
        self::ensureStarted();

        unset($_SESSION[$key]);
        self::releaseIfNeeded();
    }

    public static function pull(string $key, mixed $default = null): mixed
    {
        self::ensureStarted();

        $value = array_key_exists($key, $_SESSION)
            ? $_SESSION[$key]
            : $default;

        unset($_SESSION[$key]);

        self::releaseIfNeeded();

        return $value;
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
        self::releaseIfNeeded();
    }

    public static function destroy(): void
    {
        self::ensureStarted();

        $_SESSION = [];

        if (ini_get('session.use_cookies') === '1' && ! headers_sent())
        {
            $params = session_get_cookie_params();
            $sessionName = session_name();

            if (! is_string($sessionName))
            {
                $sessionName = self::DEFAULT_SESSION_NAME;
            }

            setcookie(
                $sessionName,
                '',
                [
                    'expires' => time() - 42_000,
                    'path' => $params['path'],
                    'domain' => $params['domain'],
                    'secure' => $params['secure'],
                    'httponly' => $params['httponly'],
                    'samesite' => $params['samesite']
                ]
            );
        }

        if (
            session_status() === PHP_SESSION_ACTIVE
            && ! session_destroy()
        ) {
            throw new RuntimeException(
                'Impossible de détruire la session.'
            );
        }

        $_SESSION = [];
    }

    // =========================================
    // INITIALISATION
    // =========================================

    private static function releaseIfNeeded(): void
    {
        // Once the router releases the lock, later access must not retain it.
        if (self::$releaseAfterAccess)
        {
            self::close();
        }
    }

    private static function ensureStarted(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE)
        {
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

        $configuration = [
            'directory' => self::directory(),
            'name' => self::sessionName(),
            'https' => $_SERVER['HTTPS'] ?? null,
            'port' => $_SERVER['SERVER_PORT'] ?? null,
            'forwarded' => $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? null,
            'trust_proxy' => config('app.trust_proxy', false),
        ];
        if (self::$configuration !== $configuration
            || session_save_path() !== $configuration['directory']
            || session_name() !== $configuration['name'])
        {
            self::configure();
            self::$configuration = $configuration;
        }

        if (! @session_start(self::$startOptions))
        {
            throw new RuntimeException('Impossible de démarrer la session.');
        }
    }

    private static function configure(): void
    {
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

        $secure = Request::capture()->isHttps();

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
            'samesite' => 'Lax'
        ]);
        self::$startOptions = [
            'use_strict_mode' => true,
            'use_only_cookies' => true,
            'use_trans_sid' => false,
            'cookie_httponly' => true,
            'cookie_secure' => $secure,
            'cookie_samesite' => 'Lax',
        ];
    }

    // =========================================
    // CONFIGURATION
    // =========================================

    private static function sessionName(): string
    {
        $sessionName = trim(
            (string) config('session.name', self::DEFAULT_SESSION_NAME)
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
