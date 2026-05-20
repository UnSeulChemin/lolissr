<?php

declare(strict_types=1);

namespace Framework\Cache;

use Framework\Config\Env;
use Framework\Support\Logger;

final class Cache
{
    private static function enabled(): bool
    {
        return Env::get('CACHE_ENABLED', false) === true;
    }

    private static function directory(): string
    {
        return ROOT . '/storage/cache';
    }

    private static function path(string $key): string
    {
        return self::directory() . '/' . sha1($key) . '.cache';
    }

    private static function ensureDirectory(): bool
    {
        $directory = self::directory();

        return is_dir($directory)
            || mkdir($directory, 0755, true);
    }

    /**
     * GET CACHE
     */
    public static function get(string $key): mixed
    {
        if (!self::enabled()) {
            return null;
        }

        $path = self::path($key);

        if (!is_file($path)) {
            return null;
        }

        $content = file_get_contents($path);

        if ($content === false) {
            Logger::warning('Cache unreadable', [
                'key' => $key,
            ]);

            return null;
        }

        try {
            $payload = json_decode(
                $content,
                false,
                512,
                JSON_THROW_ON_ERROR,
            );
        } catch (\JsonException $exception) {
            @unlink($path);

            Logger::warning('Cache corrupted JSON', [
                'key' => $key,
                'error' => $exception->getMessage(),
            ]);

            return null;
        }

        if (
            !is_object($payload)
            || !property_exists($payload, 'value')
        ) {
            @unlink($path);

            Logger::warning('Cache invalid payload', [
                'key' => $key,
            ]);

            return null;
        }

        if (($payload->expires_at ?? 0) < time()) {
            @unlink($path);

            Logger::debug('Cache expired', [
                'key' => $key,
            ]);

            return null;
        }

        Logger::debug('Cache hit', [
            'key' => $key,
        ]);

        return $payload->value;
    }

    /**
     * PUT CACHE
     */
    public static function put(
        string $key,
        mixed $value,
        ?int $ttl = null,
    ): void {
        if (!self::enabled()) {
            return;
        }

        if (!self::ensureDirectory()) {
            Logger::warning('Cache directory unavailable');

            return;
        }

        $ttl ??= (int) Env::get('CACHE_TTL', 300);

        $payload = [
            'expires_at' => time() + $ttl,
            'value' => $value,
        ];

        try {
            $json = json_encode(
                $payload,
                JSON_UNESCAPED_UNICODE
                | JSON_THROW_ON_ERROR,
            );
        } catch (\JsonException $exception) {
            Logger::warning('Cache encoding failed', [
                'key' => $key,
                'error' => $exception->getMessage(),
            ]);

            return;
        }

        $written = file_put_contents(
            self::path($key),
            $json,
            LOCK_EX,
        );

        if ($written === false) {
            Logger::warning('Cache write failed', [
                'key' => $key,
            ]);

            return;
        }

        Logger::debug('Cache stored', [
            'key' => $key,
            'ttl' => $ttl,
        ]);
    }

    /**
     * REMEMBER
     */
    public static function remember(
        string $key,
        ?int $ttl,
        callable $callback,
    ): mixed {
        if (!self::enabled()) {
            return $callback();
        }

        $cached = self::get($key);

        if ($cached !== null) {
            return $cached;
        }

        Logger::debug('Cache miss', [
            'key' => $key,
        ]);

        $value = $callback();

        self::put($key, $value, $ttl);

        return $value;
    }

    /**
     * CHECK CACHE
     */
    public static function has(string $key): bool
    {
        return is_file(
            self::path($key),
        );
    }

    /**
     * DELETE CACHE
     */
    public static function forget(string $key): void
    {
        $path = self::path($key);

        if (is_file($path)) {
            @unlink($path);
        }

        Logger::debug('Cache deleted', [
            'key' => $key,
        ]);
    }

    /**
     * CLEAR CACHE
     */
    public static function clear(): void
    {
        $directory = self::directory();

        if (!is_dir($directory)) {
            return;
        }

        $files = glob($directory . '/*.cache');

        if ($files === false) {
            $files = [];
        }

        foreach ($files as $file) {
            if (is_file($file)) {
                @unlink($file);
            }
        }

        Logger::info('Cache cleared');
    }
}
