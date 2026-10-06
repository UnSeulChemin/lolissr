<?php

declare(strict_types=1);

namespace Framework\Cache;

use Framework\Debug\Profiler;
use Framework\Logging\Logger;

use JsonException;
use Random\RandomException;

final class Cache
{
    private static ?string $directory = null;

    /** @var array<string, true> */
    private static array $computing = [];

    private function __construct()
    {
    }

    // =================================================
    // CACHE
    // =================================================

    // =================================================
    // LECTURE ET INVALIDATION
    // =================================================

    public static function remember(string $key, ?int $ttl, callable $callback): mixed
    {
        if (! self::enabled())
        {
            return $callback();
        }

        $cached = self::readEntry($key);

        if ($cached !== null)
        {
            Profiler::increment('cache.hit');

            return $cached['value'];
        }

        Profiler::increment('cache.miss');

        if (isset(self::$computing[$key]))
        {
            throw new \LogicException('Recursive cache computation for key: ' . $key);
        }

        if (! self::ensureDirectory())
        {
            return $callback();
        }

        // Garder les fichiers de verrouillage stables : les supprimer permettrait des verrous concurrents sur des inodes différents.
        $lock = @fopen(self::path($key) . '.lock', 'c');
        if ($lock === false)
        {
            return $callback();
        }

        $locked = false;
        $deadline = hrtime(true) + 2_000_000_000;
        Profiler::start('cache.lock_wait');
        try
        {
            do
            {
                $locked = flock($lock, LOCK_EX | LOCK_NB);
                if ($locked) break;
                usleep(20_000);
            } while (hrtime(true) < $deadline);
            Profiler::end('cache.lock_wait');

            // Un producteur de cache lent ne doit pas bloquer la navigation indéfiniment.
            if (! $locked)
            {
                Profiler::increment('cache.lock_timeout');
                Profiler::increment('cache.recompute');
                return $callback();
            }

            $cached = self::readEntry($key);
            if ($cached !== null)
            {
                Profiler::increment('cache.hit_after_wait');
                return $cached['value'];
            }

            $generation = self::synchronized(self::path($key), fn (): string => self::generation($key));

            self::$computing[$key] = true;
            Profiler::increment('cache.recompute');
            $value = $callback();
            if ($generation === null) return $value;

            self::synchronized(self::path($key), function () use ($key, $value, $ttl, $generation): void
            {
                if ($generation === self::generation($key))
                {
                    self::writeEntry($key, $value, $ttl);
                }
                else
                {
                    Profiler::increment('cache.discarded_write');
                }
            });
            return $value;
        }
        finally
        {
            unset(self::$computing[$key]);
            if ($locked) flock($lock, LOCK_UN);
            fclose($lock);
        }
    }

    public static function rememberRevision(string $key, string $revision, ?int $ttl, callable $callback): mixed
    {
        $build = static fn (): array => ['revision' => $revision, 'items' => $callback()];
        $cached = self::remember($key, $ttl, $build);
        if (!is_array($cached) || ($cached['revision'] ?? null) !== $revision)
        {
            self::forget($key);
            $cached = self::remember($key, $ttl, $build);
        }
        // Une requête concurrente peut publier une autre révision entre les deux lectures.
        if (!is_array($cached) || ($cached['revision'] ?? null) !== $revision || !array_key_exists('items', $cached)) return $callback();
        return $cached['items'];
    }

    public static function forget(string $key): void
    {
        self::synchronized(self::path($key), function () use ($key): void
        {
            self::advanceGeneration(self::path($key) . '.version');
            self::deleteFile(self::path($key));
        }, required: true);
    }

    /** @return array{value: mixed}|null Null means absent, not a cached null value. */
    private static function readEntry(string $key): ?array
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
                Logger::warning('Cache unreadable', ['key' => $key]);

                return null;
            }

            try
            {
                $payload = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
            }
            catch (JsonException $exception)
            {
                self::deleteObservedEntry($path, $content);

                Logger::warning('Cache corrupted JSON', ['key' => $key, 'error' => $exception->getMessage()]);

                return null;
            }

            if (! is_array($payload) || ! array_key_exists('value', $payload))
            {
                self::deleteObservedEntry($path, $content);

                Logger::warning('Cache invalid payload', ['key' => $key]);

                return null;
            }

            $expiresAt = $payload['expires_at'] ?? null;

            if (! is_int($expiresAt) && ! is_numeric($expiresAt))
            {
                self::deleteObservedEntry($path, $content);

                Logger::warning('Cache invalid expiration', ['key' => $key]);

                return null;
            }

            if ((int) $expiresAt <= time())
            {
                self::deleteObservedEntry($path, $content);

                return null;
            }

            return ['value' => $payload['value']];
        }
        finally
        {
            Profiler::end('cache.get');
        }
    }

    private static function deleteObservedEntry(string $path, string $observed): void
    {
        self::synchronized($path, static function () use ($path, $observed): void
        {
            // Une écriture peut avoir remplacé ce fichier depuis sa lecture par readEntry.
            if (@file_get_contents($path) === $observed)
            {
                self::deleteFile($path);
            }
        });
    }

    private static function writeEntry(string $key, mixed $value, ?int $ttl): void
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
            $now = time();
            // Limiter la valeur avant addition pour conserver une expiration entière dans le JSON.
            $expiresAt = $now > PHP_INT_MAX - $ttl ? PHP_INT_MAX : $now + $ttl;

            try
            {
                $json = json_encode(
                    ['expires_at' => $expiresAt, 'value' => $value],
                    JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
                );
            }
            catch (JsonException $exception)
            {
                Logger::warning('Cache encoding failed', ['key' => $key, 'error' => $exception->getMessage()]);

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
                    ['key' => $key, 'error' => $exception->getMessage()]
                );

                return;
            }

            $written = @file_put_contents($temporaryPath, $json, LOCK_EX);

            if ($written === false)
            {
                self::deleteFile($temporaryPath);

                Logger::warning('Cache write failed', ['key' => $key]);

                return;
            }

            if (@rename($temporaryPath, $path))
            {
                return;
            }

            // Sous Windows, rename() peut refuser de remplacer
            // un fichier déjà existant.
            if (is_file($path) && ! @unlink($path))
            {
                self::deleteFile($temporaryPath);

                Logger::warning('Cache replacement failed', ['key' => $key]);

                return;
            }

            if (! @rename($temporaryPath, $path))
            {
                self::deleteFile($temporaryPath);

                Logger::warning('Cache atomic rename failed', ['key' => $key]);
            }
        }
        finally
        {
            Profiler::end('cache.put');
        }
    }

    // =================================================
    // CONFIGURATION
    // =================================================

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
            Logger::warning('Cache delete failed', ['path' => $path]);
        }
    }

    /**
     * @template T
     * @param callable(): T $callback
     * @return T|null
     */
    private static function synchronized(string $path, callable $callback, bool $required = false): mixed
    {
        if (! self::ensureDirectory())
        {
            if ($required) throw new \RuntimeException('Cannot create cache directory for invalidation.');
            return null;
        }
        // Les verrous stables par entrée permettent de publier indépendamment les clés distinctes.
        $lock = @fopen($path . '.metadata.lock', 'c');
        if ($lock === false)
        {
            if ($required) throw new \RuntimeException('Cannot open cache metadata lock.');
            Profiler::increment('cache.metadata_unavailable');
            return null;
        }
        $locked = false;
        // Le cache facultatif ne doit pas retarder la réponse. L’invalidation doit réussir
        // ou signaler son échec pour éviter de servir des données périmées.
        $deadline = hrtime(true) + ($required ? 2_000_000_000 : 0);
        try
        {
            do
            {
                $locked = flock($lock, LOCK_EX | LOCK_NB);
                if ($locked) break;
                if (! $required) break;
                usleep(20_000);
            } while (hrtime(true) < $deadline);

            if (! $locked)
            {
                Profiler::increment('cache.metadata_timeout');
                if ($required) throw new \RuntimeException('Cache invalidation lock timed out after 2 seconds.');
                return null;
            }
            return $callback();
        }
        finally
        {
            if ($locked) flock($lock, LOCK_UN);
            fclose($lock);
        }
    }

    private static function generation(string $key): string
    {
        return (string) @file_get_contents(self::path($key) . '.version');
    }

    private static function advanceGeneration(string $path): void
    {
        if (file_put_contents($path, bin2hex(random_bytes(16))) === false)
        {
            throw new \RuntimeException('Cannot invalidate cache generation.');
        }
    }
}
