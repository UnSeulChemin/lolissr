<?php

declare(strict_types=1);

namespace App\Support\Media;

final class ImageAssets
{
    /** @var array<string, string>|null */
    private static ?array $fingerprints = null;

    /**
     * Reuse fingerprints only during a read-only render or DTO mapping.
     * @template T
     * @param callable(): T $render
     * @return T
     */
    public static function withFingerprints(callable $render): mixed
    {
        if (self::$fingerprints !== null) return $render();
        self::$fingerprints = [];
        try
        {
            return $render();
        }
        finally
        {
            self::$fingerprints = null;
        }
    }

    private static function fingerprint(string $file): string
    {
        if (isset(self::$fingerprints[$file])) return self::$fingerprints[$file];
        \Framework\Debug\Profiler::increment('images.fingerprint.count');
        $version = \Framework\Debug\Profiler::measure('images.fingerprint', static fn (): string|false => hash_file('sha256', $file));
        if ($version === false) throw new \RuntimeException('Cannot fingerprint image.');
        if (self::$fingerprints !== null) self::$fingerprints[$file] = $version;
        return $version;
    }

    public static function url(string $url, bool $grid = false): string
    {
        $path = parse_url($url, PHP_URL_PATH);
        if (!is_string($path)) return $url;
        $position = strpos($path, 'images/');
        if ($position === false) return $url;
        $relative = substr($path, $position);
        if (str_contains($relative, '..') || str_contains($relative, '\\')) return $url;
        $file = dirname(__DIR__, 3) . '/public/' . $relative;
        if ($grid && !str_contains($relative, 'images/profil/'))
        {
            $candidate = preg_replace('/\.(jpg|jpeg|png|webp)$/i', '.grid.$1', $file);
            if (is_string($candidate) && is_file($candidate))
            {
                $file = $candidate;
                $url = preg_replace('/\.(jpg|jpeg|png|webp)(?=\?|$)/i', '.grid.$1', $url) ?? $url;
            }
        }
        if (!is_file($file)) return $url;
        $url = preg_replace('/([?&])v=[^&]*&?/', '$1', $url) ?? $url;
        $url = rtrim($url, '?&');
        $version = self::fingerprint($file);
        return $url . (str_contains($url, '?') ? '&' : '?') . 'v=' . $version;
    }

    public static function profileVersion(): string
    {
        $manifest = dirname(__DIR__, 3) . '/storage/profile-images.json';
        if (is_file($manifest))
        {
            $data = json_decode((string) file_get_contents($manifest), true);
            if (is_array($data) && ($data['format'] ?? null) === 2 && isset($data['version']) && is_string($data['version'])) return $data['version'];
        }
        return self::rebuildProfileVersion();
    }

    public static function invalidateProfileVersion(): void
    {
        $manifest = dirname(__DIR__, 3) . '/storage/profile-images.json';
        if (is_file($manifest) && !unlink($manifest)) throw new \RuntimeException('Cannot invalidate profile image manifest.');
    }

    public static function rebuildProfileVersion(): string
    {
        $versions = [];
        foreach (['avatar', 'banner', 'frame'] as $type)
        {
            $files = glob(dirname(__DIR__, 3) . '/public/images/profil/' . $type . '/thumbnail/*');
            foreach ($files === false ? [] : $files as $file)
            {
                if (!is_file($file)) continue;
                $hash = hash_file('sha256', $file);
                if ($hash === false) throw new \RuntimeException('Cannot fingerprint profile image.');
                $versions[] = $type . '/' . basename($file) . ':' . $hash;
            }
        }
        $version = hash('sha256', implode('|', $versions));
        ImageManifest::write(dirname(__DIR__, 3) . '/storage/profile-images.json', ['format' => 2, 'version' => $version]);
        return $version;
    }
}
