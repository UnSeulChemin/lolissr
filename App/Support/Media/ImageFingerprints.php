<?php

declare(strict_types=1);

namespace App\Support\Media;

use Framework\Debug\Profiler;

final class ImageFingerprints
{
    private static function entry(string $file): ?string
    {
        if (!in_array(strtolower(pathinfo($file, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'webp', 'gif'], true)) return null;
        $root = dirname(__DIR__, 3);
        $resolved = realpath($file);
        $images = realpath($root . '/public/images');
        if ($resolved === false || $images === false || !str_starts_with($resolved, $images . DIRECTORY_SEPARATOR)) return null;
        return $root . '/storage/image-fingerprints/' . hash('sha256', $resolved) . '.json';
    }

    /** @return array{size: int, mtime: int, ctime: int, ino: int}|null */
    private static function signature(string $file): ?array
    {
        clearstatcache(true, $file);
        $stat = @stat($file);
        return $stat === false ? null : ['size' => $stat['size'], 'mtime' => $stat['mtime'], 'ctime' => $stat['ctime'], 'ino' => $stat['ino']];
    }

    private static function cached(string $entry, string $file): ?string
    {
        $contents = @file_get_contents($entry);
        $data = $contents === false ? null : json_decode($contents, true);
        if (!is_array($data) || ($data['format'] ?? null) !== 1 || ($data['signature'] ?? null) !== self::signature($file)
            || !is_string($data['hash'] ?? null) || preg_match('/^[a-f0-9]{64}$/D', $data['hash']) !== 1) return null;
        return $data['hash'];
    }

    public static function get(string $file, bool $refresh = false): string
    {
        $entry = self::entry($file);
        if (!$refresh && $entry !== null)
        {
            $cached = self::cached($entry, $file);
            if ($cached !== null)
            {
                Profiler::increment('images.fingerprint.hit');
                return $cached;
            }
        }
        $lock = false;
        if ($entry !== null)
        {
            $directory = dirname($entry);
            if (is_dir($directory) || @mkdir($directory, 0755, true) || is_dir($directory))
                $lock = @fopen($entry . '.lock', 'c');
        }
        $locked = $lock !== false && flock($lock, $refresh ? LOCK_EX : LOCK_EX | LOCK_NB);
        try
        {
            if ($refresh && $entry !== null && !$locked) throw new \RuntimeException('Cannot lock image fingerprint manifest.');
            if (!$refresh && $locked && $entry !== null)
            {
                $cached = self::cached($entry, $file);
                if ($cached !== null) return $cached;
            }
            $before = self::signature($file);
            Profiler::increment('images.fingerprint.count');
            $hash = Profiler::measure('images.fingerprint', static fn (): string|false => hash_file('sha256', $file));
            if ($hash === false) throw new \RuntimeException('Cannot fingerprint image.');
            if ($refresh && $before !== self::signature($file)) throw new \RuntimeException('Image changed during fingerprint refresh. Retry.');
            // Publish only a stable observation. A contended/unavailable manifest falls back to hashing.
            if ($locked && $entry !== null && $before !== null && $before === self::signature($file))
            {
                try
                {
                    ImageManifest::write($entry, ['format' => 1, 'signature' => $before, 'hash' => $hash]);
                }
                catch (\RuntimeException)
                {
                    if ($refresh) throw new \RuntimeException('Cannot refresh image fingerprint manifest.');
                    Profiler::increment('images.fingerprint.publish_failed');
                }
            }
            return $hash;
        }
        finally
        {
            if ($lock !== false)
            {
                if ($locked) flock($lock, LOCK_UN);
                fclose($lock);
            }
        }
    }

    public static function refresh(string $file): void
    {
        if (self::entry($file) === null) return;
        self::get($file, refresh: true);
    }

    public static function forget(string $file): void
    {
        $entry = self::entry($file);
        if ($entry !== null && is_file($entry) && !unlink($entry))
            throw new \RuntimeException('Cannot remove image fingerprint.');
    }

    public static function rebuild(): int
    {
        $directory = dirname(__DIR__, 3) . '/public/images';
        $count = 0;
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS));
        foreach ($files as $file)
        {
            if (!$file->isFile() || $file->isLink() || !in_array(strtolower($file->getExtension()), ['jpg', 'jpeg', 'png', 'webp', 'gif'], true)) continue;
            self::get($file->getPathname(), refresh: true);
            $count++;
        }
        return $count;
    }
}
