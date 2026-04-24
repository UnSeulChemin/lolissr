<?php

declare(strict_types=1);

namespace App\Core\Support;

use Throwable;

final class Logger
{
    private static function logDirectory(): string
    {
        return (string) env('LOG_DIR', app_path('Storage/logs'));
    }

    private static function logFile(): string
    {
        return self::logDirectory() . DIRECTORY_SEPARATOR . 'app.log';
    }

    private static function write(
        string $level,
        string $message,
        array $context = []
    ): void {
        $logDir = self::logDirectory();
        $logFile = self::logFile();

        if (!is_dir($logDir))
        {
            $created = mkdir($logDir, 0755, true);

            if (!$created && !is_dir($logDir))
            {
                return;
            }
        }

        $payload = [
            'date' => date('Y-m-d H:i:s'),
            'level' => $level,
            'message' => $message,
            'context' => $context,
            'request' => [
                'method' => $_SERVER['REQUEST_METHOD'] ?? null,
                'uri' => $_SERVER['REQUEST_URI'] ?? null,
                'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
            ],
        ];

        @file_put_contents(
            $logFile,
            json_encode(
                $payload,
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            ) . PHP_EOL,
            FILE_APPEND | LOCK_EX
        );
    }

    public static function debug(string $message, array $context = []): void
    {
        self::write('DEBUG', $message, $context);
    }

    public static function info(string $message, array $context = []): void
    {
        self::write('INFO', $message, $context);
    }

    public static function warning(string $message, array $context = []): void
    {
        self::write('WARNING', $message, $context);
    }

    public static function error(string $message, array $context = []): void
    {
        self::write('ERROR', $message, $context);
    }

    public static function exception(Throwable $exception, array $context = []): void
    {
        self::error($exception->getMessage(), array_merge($context, [
            'exception' => $exception::class,
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
        ]));
    }
}