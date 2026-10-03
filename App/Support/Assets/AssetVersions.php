<?php

declare(strict_types=1);

namespace App\Support\Assets;

use Framework\Config\ApplicationConfig;

use RuntimeException;

final class AssetVersions
{
    /** @var array<string, string> */
    private static array $versions = [];

    public static function version(string $path): string
    {
        if (isset(self::$versions[$path])) return self::$versions[$path];

        if (ApplicationConfig::isProduction())
        {
            /** @var array<string, string> $manifest */
            $manifest = config('assets', []);
            if (isset($manifest[$path])) return self::$versions[$path] = $manifest[$path];
        }

        $version = hash_file('sha256', dirname(__DIR__, 3) . '/public/' . $path);
        if ($version === false) throw new RuntimeException('Asset introuvable : ' . $path);
        return self::$versions[$path] = $version;
    }
}
