<?php

declare(strict_types=1);

namespace App\Core\Support;

use JsonException;
use Throwable;

final class Logger
{
    private static function enabled(): bool
    {
        return env_bool(
            'LOG_ENABLED',
            true
        );
    }

    private static function directory(): string
    {
        return ROOT . '/storage/logs';
    }

    private static function file(): string
    {
        return self::directory()
            . '/app.log';
    }

    private static function ensureDirectory(): bool
    {
        $directory = self::directory();

        if (is_dir($directory))
        {
            return true;
        }

        return mkdir(
            $directory,
            0755,
            true
        ) || is_dir($directory);
    }

    private static function write(
        string $level,
        string $message,
        array $context = []
    ): void {
        if (!self::enabled())
        {
            return;
        }

        /*
        |-----------------------------------------
        | Désactive DEBUG en production
        |-----------------------------------------
        */

        if (
            strtoupper($level) === 'DEBUG'
            && !env_bool('APP_DEBUG')
        ) {
            return;
        }

        if (!self::ensureDirectory())
        {
            return;
        }

        $payload = [
            'date' => date('Y-m-d H:i:s'),
            'level' => strtoupper($level),
            'message' => $message,
            'context' => $context,
            'request' => [
                'method' => $_SERVER['REQUEST_METHOD'] ?? null,
                'uri' => $_SERVER['REQUEST_URI'] ?? null,
                'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
            ],
        ];

        try
        {
            $content = json_encode(
                $payload,
                JSON_UNESCAPED_UNICODE
                | JSON_UNESCAPED_SLASHES
                | JSON_THROW_ON_ERROR
            );
        }
        catch (JsonException)
        {
            return;
        }

        file_put_contents(
            self::file(),
            $content . PHP_EOL,
            FILE_APPEND | LOCK_EX
        );
    }

    public static function debug(
        string $message,
        array $context = []
    ): void {
        self::write(
            'DEBUG',
            $message,
            $context
        );
    }

    public static function info(
        string $message,
        array $context = []
    ): void {
        self::write(
            'INFO',
            $message,
            $context
        );
    }

    public static function warning(
        string $message,
        array $context = []
    ): void {
        self::write(
            'WARNING',
            $message,
            $context
        );
    }

    public static function error(
        string $message,
        array $context = []
    ): void {
        self::write(
            'ERROR',
            $message,
            $context
        );
    }

    public static function exception(
        Throwable $exception,
        array $context = []
    ): void {
        self::error(
            $exception->getMessage(),
            array_merge($context, [
                'exception' => $exception::class,
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
            ])
        );
    }
}