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
        return ROOT . '/storage/cache';
    }

    private static function path(string $key): string
    {
        return self::directory() . '/' . sha1($key) . '.cache';
    }

    private static function ensureDirectory(): bool
    {
        $dir = self::directory();

        return is_dir($dir) || mkdir($dir, 0755, true);
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
            return null;
        }

        $payload = json_decode($content);

        if (!is_object($payload) || !property_exists($payload, 'value')) {
            Logger::warning('Cache invalid payload', ['key' => $key]);
            return null;
        }

        if (($payload->expires_at ?? 0) < time()) {
            @unlink($path);

            Logger::debug('Cache expired', ['key' => $key]);
            return null;
        }

        Logger::debug('Cache hit', ['key' => $key]);

        return $payload->value;
    }

    /**
     * PUT CACHE
     */
    public static function put(string $key, mixed $value, ?int $ttl = null): void
    {
        if (!self::enabled()) {
            return;
        }

        if (!self::ensureDirectory()) {
            return;
        }

        $ttl ??= (int) Env::get('CACHE_TTL', 300);

        $payload = (object) [
            'expires_at' => time() + $ttl,
            'value' => $value,
        ];

        file_put_contents(
            self::path($key),
            json_encode($payload, JSON_UNESCAPED_UNICODE),
            LOCK_EX
        );

        Logger::debug('Cache stored', [
            'key' => $key,
            'ttl' => $ttl
        ]);
    }

    /**
     * REMEMBER
     */
    public static function remember(string $key, ?int $ttl, callable $callback): mixed
    {
        if (!self::enabled()) {
            return $callback();
        }

        $cached = self::get($key);

        if ($cached !== null) {
            return $cached;
        }

        Logger::debug('Cache miss', ['key' => $key]);

        $value = $callback();

        self::put($key, $value, $ttl);

        return $value;
    }

    public static function has(string $key): bool
    {
        return is_file(self::path($key));
    }

    public static function forget(string $key): void
    {
        $path = self::path($key);

        if (is_file($path)) {
            @unlink($path);
        }

        Logger::debug('Cache deleted', ['key' => $key]);
    }

    public static function clear(): void
    {
        $dir = self::directory();

        if (!is_dir($dir)) {
            return;
        }

        foreach (glob($dir . '/*.cache') ?: [] as $file) {
            if (is_file($file)) {
                @unlink($file);
            }
        }

        Logger::info('Cache cleared');
    }
}