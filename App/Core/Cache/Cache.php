<?php

declare(strict_types=1);

namespace App\Core\Cache;

use App\Core\Config\Env;
use App\Core\Support\Logger;

final class Cache
{
    private static function enabled(): bool
    {
        return Env::get('CACHE_ENABLED', false) === true;
    }

    private static function directory(): string
    {
        return ROOT . '/Storage/cache';
    }

    private static function path(string $key): string
    {
        $safeKey = sha1($key);

        return self::directory() . '/' . $safeKey . '.cache';
    }

    public static function get(string $key): mixed
    {
        if (!self::enabled())
        {
            return null;
        }

        $path = self::path($key);

        if (!is_file($path))
        {
            return null;
        }

        $content = file_get_contents($path);

        if ($content === false)
        {
            return null;
        }

        $payload = @unserialize($content);

        if (!is_array($payload))
        {
            Logger::warning('Cache payload invalid', [
                'key' => $key,
            ]);

            return null;
        }

        if (($payload['expires_at'] ?? 0) < time())
        {
            @unlink($path);

            Logger::debug('Cache expired', [
                'key' => $key,
            ]);

            return null;
        }

        Logger::debug('Cache hit', [
            'key' => $key,
        ]);

        return $payload['value'] ?? null;
    }

    public static function put(
        string $key,
        mixed $value,
        ?int $ttl = null
    ): void {
        if (!self::enabled())
        {
            return;
        }

        $directory = self::directory();

        if (!is_dir($directory))
        {
            mkdir($directory, 0775, true);
        }

        $ttl ??= (int) Env::get('CACHE_TTL', 300);

        $payload = [
            'expires_at' => time() + $ttl,
            'value' => $value,
        ];

        file_put_contents(
            self::path($key),
            serialize($payload),
            LOCK_EX
        );

        Logger::debug('Cache stored', [
            'key' => $key,
            'ttl' => $ttl,
        ]);
    }

    public static function remember(
        string $key,
        ?int $ttl,
        callable $callback
    ): mixed {
        if (!self::enabled())
        {
            return $callback();
        }

        $cached = self::get($key);

        /*
        |-----------------------------------------
        | Important : null doit être accepté
        |-----------------------------------------
        */

        if ($cached !== null || self::has($key))
        {
            return $cached;
        }

        Logger::debug('Cache miss', [
            'key' => $key,
        ]);

        $value = $callback();

        self::put($key, $value, $ttl);

        return $value;
    }

    public static function has(string $key): bool
    {
        $path = self::path($key);

        return is_file($path);
    }

    public static function forget(string $key): void
    {
        $path = self::path($key);

        if (is_file($path))
        {
            unlink($path);

            Logger::debug('Cache deleted', [
                'key' => $key,
            ]);
        }
    }

    public static function clear(): void
    {
        $directory = self::directory();

        if (!is_dir($directory))
        {
            return;
        }

        foreach (glob($directory . '/*.cache') ?: [] as $file)
        {
            unlink($file);
        }

        Logger::info('Cache cleared');
    }
}