<?php

declare(strict_types=1);

namespace Framework\Support;

use Framework\Application\App;
use Framework\Container\AppContainer;
use Framework\Http\Request;

use JsonException;
use Throwable;

final class Logger
{
    private const FILE_PREFIX = 'app-';
    private const FILE_EXTENSION = '.log';

    private static ?string $directory = null;

    private static bool $cleaned = false;

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

        return is_dir($directory)
            || mkdir($directory, 0755, true)
            || is_dir($directory);
    }

    private static function cleanExpiredFiles(): void
    {
        if (self::$cleaned)
        {
            return;
        }

        self::$cleaned = true;

        $files = glob(
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

            $modifiedAt = filemtime($file);

            if ($modifiedAt === false || $modifiedAt >= $expirationTimestamp)
            {
                continue;
            }

            if (! unlink($file))
            {
                error_log('Logger: impossible de supprimer le fichier expiré : ' . $file);
            }
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
        try
        {
            if (! AppContainer::has())
            {
                return null;
            }

            $request = app(Request::class);

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
            return;
        }

        self::cleanExpiredFiles();

        try
        {
            $content = json_encode(
                [
                    'date' => date('Y-m-d H:i:s'),
                    'level' => $level,
                    'message' => $message,
                    'context' => $context,
                    'request' => self::requestContext(),
                ],
                JSON_PRETTY_PRINT
                | JSON_UNESCAPED_UNICODE
                | JSON_UNESCAPED_SLASHES
                | JSON_INVALID_UTF8_SUBSTITUTE
                | JSON_THROW_ON_ERROR
            );
        }
        catch (JsonException)
        {
            return;
        }

        $written = file_put_contents(
            self::file(),
            $content . PHP_EOL,
            FILE_APPEND | LOCK_EX
        );

        if ($written === false && App::debug())
        {
            error_log(
                'LoliSSR Logger: impossible d\'écrire dans le fichier de log : '
                . self::file()
            );
        }
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
            array_merge(
                $context,
                [
                    'exception' => $exception::class,
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine(),
                    'trace' => $exception->getTraceAsString(),
                ]
            )
        );
    }
}