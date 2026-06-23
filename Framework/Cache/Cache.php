<?php

declare(strict_types=1);

namespace Framework\Cache;

use Framework\Support\Logger;

use JsonException;

final class Cache
{
    private static ?string $directory = null;

    private static function enabled(): bool
    {
        return (bool) config('cache.enabled', false);
    }

    private static function ttl(): int
    {
        return max(
            1,
            (int) config(
                'cache.ttl',
                300,
            ),
        );
    }

    private static function directory(): string
    {
        return self::$directory
            ??= base_path(
                'storage/cache',
            );
    }

    private static function path(
        string $key,
    ): string {
        return self::directory()
            . DIRECTORY_SEPARATOR
            . sha1($key)
            . '.cache';
    }

    private static function ensureDirectory(): bool
    {
        $directory =
            self::directory();

        return is_dir($directory)
            || mkdir(
                $directory,
                0755,
                true,
            );
    }

    private static function deleteFile(
        string $path,
    ): void {
        @unlink(
            $path,
        );
    }

    public static function get(
        string $key,
    ): mixed {

        if (! self::enabled())
        {
            return null;
        }

        $path =
            self::path(
                $key,
            );

        if (! is_file($path))
        {
            return null;
        }

        $content =
            file_get_contents(
                $path,
            );

        if ($content === false)
        {
            Logger::warning(
                'Cache unreadable',
                [
                    'key' => $key,
                ],
            );

            return null;
        }

        try {

            $payload =
                json_decode(
                    $content,
                    false,
                    512,
                    JSON_THROW_ON_ERROR,
                );

        }
        catch (JsonException $exception)
        {
            self::deleteFile(
                $path,
            );

            Logger::warning(
                'Cache corrupted JSON',
                [
                    'key' => $key,
                    'error' => $exception->getMessage(),
                ],
            );

            return null;
        }

        if (! is_object($payload) || ! property_exists($payload, 'value'))
        {
            self::deleteFile($path);

            Logger::warning('Cache invalid payload', ['key' => $key]);

            return null;
        }

        if (($payload->expires_at ?? 0) < time())
        {
            self::deleteFile($path);

            return null;
        }

        return $payload->value;
    }

    public static function put(string $key, mixed $value, ?int $ttl = null): void
    {
        if (! self::enabled())
        {
            return;
        }

        if (! self::ensureDirectory())
        {
            Logger::warning('Cache directory unavailable');

            return;
        }

        $ttl ??= self::ttl();

        try
        {
            $json = json_encode(['expires_at' => time() + $ttl, 'value' => $value], JSON_UNESCAPED_UNICODE  | JSON_THROW_ON_ERROR);
        }
        catch (JsonException $exception)
        {
            Logger::warning('Cache encoding failed', ['key' => $key, 'error' => $exception->getMessage()]);

            return;
        }

        $written = file_put_contents(self::path($key), $json, LOCK_EX);

        if ($written === false)
        {
            Logger::warning('Cache write failed', ['key' => $key]);
        }
    }

    public static function remember(string $key, ?int $ttl, callable $callback): mixed
    {
        if (! self::enabled())
        {
            return $callback();
        }

        $cached = self::get($key);

        if ($cached !== null)
        {
            return $cached;
        }

        $value = $callback();

        self::put($key, $value, $ttl);

        return $value;
    }

    public static function has(string $key): bool
    {
        if (! self::enabled())
        {
            return false;
        }

        return is_file(self::path($key));
    }

    public static function forget(string $key): void
    {
        self::deleteFile(self::path($key));
    }

    public static function clear(): void
    {
        $directory = self::directory();

        if (! is_dir($directory))
        {
            return;
        }

        $files = glob($directory . DIRECTORY_SEPARATOR . '*.cache');

        if ($files === false)
        {
            $files = [];
        }

        foreach ($files as $file)
        {
            self::deleteFile($file);
        }

        Logger::info('Cache cleared');
    }
}
