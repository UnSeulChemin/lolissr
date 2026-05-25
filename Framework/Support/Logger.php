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
    private static function enabled(): bool
    {
        return (bool) config(
            'log.enabled',
            true,
        );
    }

    private static function directory(): string
    {
        return base_path(
            'storage/logs',
        );
    }

    private static function file(): string
    {
        return self::directory()
            . DIRECTORY_SEPARATOR
            . 'app.log';
    }

    private static function ensureDirectory(): bool
    {
        $directory = self::directory();

        if (is_dir($directory)) {
            return true;
        }

        return mkdir(
            $directory,
            0755,
            true,
        ) || is_dir($directory);
    }

    /**
     * @return array<string, mixed>|null
     */
    private static function requestContext(): ?array
    {
        try {

            if (!AppContainer::has()) {
                return null;
            }

            /** @var Request|null $request */
            $request = app(Request::class);

            if (!$request instanceof Request) {
                return null;
            }

            return [
                'method' => $request->method(),
                'uri' => $request->uri(),
                'ip' => $request->server(
                    'REMOTE_ADDR',
                ),
            ];

        } catch (Throwable) {
            return null;
        }
    }

    /**
     * @param array<string, mixed> $context
     */
    private static function write(
        string $level,
        string $message,
        array $context = [],
    ): void {

        if (!self::enabled()) {
            return;
        }

        if (
            strtoupper($level) === 'DEBUG'
            && !App::debug()
        ) {
            return;
        }

        if (!self::ensureDirectory()) {
            return;
        }

        $payload = [
            'date' => date('Y-m-d H:i:s'),

            'level' => strtoupper($level),

            'message' => trim($message),

            'context' => $context,

            'request' => self::requestContext(),
        ];

        try {

            $content = json_encode(
                $payload,
                JSON_UNESCAPED_UNICODE
                | JSON_UNESCAPED_SLASHES
                | JSON_THROW_ON_ERROR,
            );

        } catch (JsonException) {
            return;
        }

        if ($content === false) {
            return;
        }

        file_put_contents(
            self::file(),
            $content . PHP_EOL,
            FILE_APPEND | LOCK_EX,
        );
    }

    /**
     * @param array<string, mixed> $context
     */
    public static function debug(
        string $message,
        array $context = [],
    ): void {
        self::write(
            'DEBUG',
            $message,
            $context,
        );
    }

    /**
     * @param array<string, mixed> $context
     */
    public static function info(
        string $message,
        array $context = [],
    ): void {
        self::write(
            'INFO',
            $message,
            $context,
        );
    }

    /**
     * @param array<string, mixed> $context
     */
    public static function warning(
        string $message,
        array $context = [],
    ): void {
        self::write(
            'WARNING',
            $message,
            $context,
        );
    }

    /**
     * @param array<string, mixed> $context
     */
    public static function error(
        string $message,
        array $context = [],
    ): void {
        self::write(
            'ERROR',
            $message,
            $context,
        );
    }

    /**
     * @param array<string, mixed> $context
     */
    public static function exception(
        Throwable $exception,
        array $context = [],
    ): void {

        self::error(
            $exception->getMessage(),
            array_merge(
                $context,
                [
                    'exception' => $exception::class,

                    'file' => $exception->getFile(),

                    'line' => $exception->getLine(),

                    'trace' => $exception->getTraceAsString(),
                ],
            ),
        );
    }
}