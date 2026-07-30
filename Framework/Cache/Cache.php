<?php

declare(strict_types=1);

namespace Framework\Cache;

use Framework\Debug\Profiler;
use Framework\Support\Logger;

use JsonException;
use Random\RandomException;

final class Cache
{
    private static ?string $directory = null;

    private function __construct()
    {
    }

    // =========================================
    // CACHE
    // =========================================

    public static function get(string $key): mixed
    {
        if (! self::enabled())
        {
            return null;
        }

        Profiler::start('cache.get');

        try
        {
            $path = self::path($key);

            if (! is_file($path))
            {
                return null;
            }

            $content = @file_get_contents($path);

            if ($content === false)
            {
                Logger::warning(
                    'Cache unreadable',
                    [
                        'key' => $key
                    ]
                );

                return null;
            }

            try
            {
                $payload = json_decode(
                    $content,
                    true,
                    512,
                    JSON_THROW_ON_ERROR
                );
            }
            catch (JsonException $exception)
            {
                self::deleteFile($path);

                Logger::warning(
                    'Cache corrupted JSON',
                    [
                        'key' => $key,
                        'error' => $exception->getMessage()
                    ]
                );

                return null;
            }

            if (! is_array($payload) || ! array_key_exists('value', $payload))
            {
                self::deleteFile($path);

                Logger::warning(
                    'Cache invalid payload',
                    [
                        'key' => $key
                    ]
                );

                return null;
            }

            $expiresAt = $payload['expires_at'] ?? null;

            if (! is_int($expiresAt) && ! is_numeric($expiresAt))
            {
                self::deleteFile($path);

                Logger::warning(
                    'Cache invalid expiration',
                    [
                        'key' => $key
                    ]
                );

                return null;
            }

            if ((int) $expiresAt <= time())
            {
                self::deleteFile($path);

                return null;
            }

            return $payload['value'];
        }
        finally
        {
            Profiler::end('cache.get');
        }
    }

    public static function put(string $key, mixed $value, ?int $ttl = null): void
    {
        if (! self::enabled())
        {
            return;
        }

        Profiler::start('cache.put');

        try
        {
            if (! self::ensureDirectory())
            {
                Logger::warning('Cache directory unavailable');

                return;
            }

            $ttl = max(1, $ttl ?? self::ttl());

            try
            {
                $json = json_encode(
                    [
                        'expires_at' => time() + $ttl,
                        'value' => $value
                    ],
                    JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
                );
            }
            catch (JsonException $exception)
            {
                Logger::warning(
                    'Cache encoding failed',
                    [
                        'key' => $key,
                        'error' => $exception->getMessage()
                    ]
                );

                return;
            }

            $path = self::path($key);

            try
            {
                $temporaryPath = $path . '.' . bin2hex(random_bytes(6)) . '.tmp';
            }
            catch (RandomException $exception)
            {
                Logger::warning(
                    'Cache temporary filename generation failed',
                    [
                        'key' => $key,
                        'error' => $exception->getMessage()
                    ]
                );

                return;
            }

            $written = @file_put_contents($temporaryPath, $json, LOCK_EX);

            if ($written === false)
            {
                self::deleteFile($temporaryPath);

                Logger::warning(
                    'Cache write failed',
                    [
                        'key' => $key
                    ]
                );

                return;
            }

            if (@rename($temporaryPath, $path))
            {
                return;
            }

            /*
             * Sous Windows, rename() peut refuser de remplacer
             * un fichier déjà existant.
             */
            if (is_file($path) && ! @unlink($path))
            {
                self::deleteFile($temporaryPath);

                Logger::warning(
                    'Cache replacement failed',
                    [
                        'key' => $key
                    ]
                );

                return;
            }

            if (! @rename($temporaryPath, $path))
            {
                self::deleteFile($temporaryPath);

                Logger::warning(
                    'Cache atomic rename failed',
                    [
                        'key' => $key
                    ]
                );
            }
        }
        finally
        {
            Profiler::end('cache.put');
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
            Profiler::increment('cache.hit');

            return $cached;
        }

        Profiler::increment('cache.miss');

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

        return self::get($key) !== null;
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
            return;
        }

        foreach ($files as $file)
        {
            self::deleteFile($file);
        }

        Logger::info('Cache cleared');
    }

    // =========================================
    // CONFIGURATION
    // =========================================

    private static function enabled(): bool
    {
        return (bool) config('cache.enabled', false);
    }

    private static function ttl(): int
    {
        return max(1, (int) config('cache.ttl', 300));
    }

    private static function directory(): string
    {
        return self::$directory ??= base_path('storage/cache');
    }

    private static function path(string $key): string
    {
        return self::directory() . DIRECTORY_SEPARATOR . sha1($key) . '.cache';
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

    private static function deleteFile(string $path): void
    {
        if (! is_file($path))
        {
            return;
        }

        if (! @unlink($path) && is_file($path))
        {
            Logger::warning(
                'Cache delete failed',
                [
                    'path' => $path
                ]
            );
        }
    }
}