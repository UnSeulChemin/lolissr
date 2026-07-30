<?php

declare(strict_types=1);

namespace Framework\Support;

use Framework\Application\App;
use Framework\Container\AppContainer;
use Framework\Http\Request;
use Framework\Http\RequestContext;

use JsonException;
use Throwable;

final class Logger
{
    private const FILE_PREFIX = 'app-';
    private const FILE_EXTENSION = '.log';

    private const SENSITIVE_KEYS = [
        'password',
        'password_confirmation',
        'current_password',
        'new_password',
        'csrf_token',
        'csrf-token',
        'token',
        'access_token',
        'refresh_token',
        'cookie',
        'set-cookie',
        'authorization',
        'db_pass',
        'db_password',
        'api_key',
        'secret',
    ];

    private static ?string $directory = null;

    private static bool $cleaned = false;

    private function __construct()
    {
    }

    // =========================================
    // NIVEAUX
    // =========================================

    /**
     * @param array<string, mixed> $context
     */
    public static function debug(string $message, array $context = []): void
    {
        self::write('DEBUG', $message, $context);
    }

    /**
     * @param array<string, mixed> $context
     */
    public static function info(string $message, array $context = []): void
    {
        self::write('INFO', $message, $context);
    }

    /**
     * @param array<string, mixed> $context
     */
    public static function warning(string $message, array $context = []): void
    {
        self::write('WARNING', $message, $context);
    }

    /**
     * @param array<string, mixed> $context
     */
    public static function error(string $message, array $context = []): void
    {
        self::write('ERROR', $message, $context);
    }

    // =========================================
    // EXCEPTIONS
    // =========================================

    /**
     * @param array<string, mixed> $context
     */
    public static function exception(Throwable $exception, array $context = []): void
    {
        self::error(
            $exception->getMessage(),
            [
                ...$context,
                'exception' => $exception::class,
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
            ]
        );
    }

    // =========================================
    // ÉCRITURE
    // =========================================

    /**
     * @param array<string, mixed> $context
     */
    private static function write(string $level, string $message, array $context = []): void
    {
        if (! self::enabled())
        {
            return;
        }

        $level = strtoupper(trim($level));

        if ($level === 'DEBUG' && ! App::debug())
        {
            return;
        }

        $message = trim($message);

        if ($message === '')
        {
            return;
        }

        if (! self::ensureDirectory())
        {
            self::reportInternalError(
                'Impossible de créer le dossier de logs : ' . self::directory()
            );

            return;
        }

        self::cleanExpiredFiles();

        try
        {
            $content = json_encode(
                [
                    'request_id' => RequestContext::requestId(),
                    'date' => date('Y-m-d H:i:s'),
                    'level' => $level,
                    'message' => $message,
                    'context' => self::sanitize($context),
                    'request' => self::requestContext(),
                ],
                JSON_UNESCAPED_UNICODE
                | JSON_UNESCAPED_SLASHES
                | JSON_INVALID_UTF8_SUBSTITUTE
                | JSON_THROW_ON_ERROR
            );
        }
        catch (JsonException $exception)
        {
            self::reportInternalError(
                'Encodage JSON impossible : ' . $exception->getMessage()
            );

            return;
        }

        $file = self::file();

        $written = @file_put_contents(
            $file,
            $content . PHP_EOL,
            FILE_APPEND | LOCK_EX
        );

        if ($written === false)
        {
            self::reportInternalError(
                'Impossible d’écrire dans le fichier de log : ' . $file
            );
        }
    }

    // =========================================
    // CONTEXTE
    // =========================================

    /**
     * @return array<string, mixed>|null
     */
    private static function requestContext(): ?array
    {
        if (! AppContainer::has())
        {
            return null;
        }

        try
        {
            $request = AppContainer::get()->get(Request::class);

            if (! $request instanceof Request)
            {
                return null;
            }

            return [
                'method' => $request->method(),
                'uri' => $request->uri(),
                'ip' => $request->server('REMOTE_ADDR'),
            ];
        }
        catch (Throwable)
        {
            return null;
        }
    }

    /**
     * @param array<mixed> $context
     *
     * @return array<mixed>
     */
    private static function sanitize(array $context): array
    {
        foreach ($context as $key => $value)
        {
            $normalizedKey = strtolower(trim((string) $key));

            if (in_array($normalizedKey, self::SENSITIVE_KEYS, true))
            {
                $context[$key] = '[REDACTED]';

                continue;
            }

            if (is_array($value))
            {
                $context[$key] = self::sanitize($value);
            }
        }

        return $context;
    }

    // =========================================
    // FICHIERS
    // =========================================

    private static function directory(): string
    {
        return self::$directory ??= base_path('storage/logs');
    }

    private static function file(): string
    {
        return self::directory()
            . DIRECTORY_SEPARATOR
            . self::FILE_PREFIX
            . date('Y-m-d')
            . self::FILE_EXTENSION;
    }

    private static function ensureDirectory(): bool
    {
        $directory = self::directory();

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

    private static function cleanExpiredFiles(): void
    {
        if (self::$cleaned)
        {
            return;
        }

        self::$cleaned = true;

        $files = @glob(
            self::directory()
            . DIRECTORY_SEPARATOR
            . self::FILE_PREFIX
            . '*'
            . self::FILE_EXTENSION
        );

        if ($files === false)
        {
            return;
        }

        $expirationTimestamp = time() - (self::retentionDays() * 86400);

        foreach ($files as $file)
        {
            if (! is_file($file))
            {
                continue;
            }

            $modifiedAt = @filemtime($file);

            if ($modifiedAt === false || $modifiedAt >= $expirationTimestamp)
            {
                continue;
            }

            if (! @unlink($file) && is_file($file))
            {
                self::reportInternalError(
                    'Impossible de supprimer le fichier expiré : ' . $file
                );
            }
        }
    }

    // =========================================
    // CONFIGURATION
    // =========================================

    private static function enabled(): bool
    {
        return (bool) config('log.enabled', true);
    }

    private static function retentionDays(): int
    {
        return max(1, (int) config('log.retention_days', 14));
    }

    // =========================================
    // SECOURS
    // =========================================

    private static function reportInternalError(string $message): void
    {
        if (! App::debug())
        {
            return;
        }

        error_log('Framework Logger: ' . $message);
    }
}