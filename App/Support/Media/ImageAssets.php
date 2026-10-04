<?php

declare(strict_types=1);

namespace App\Support\Media;

final class ImageAssets
{
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
        return $url . (str_contains($url, '?') ? '&' : '?') . 'v=' . filemtime($file) . '-' . filesize($file);
    }

    public static function profileVersion(): string
    {
        $versions = [];
        foreach (['avatar', 'banner', 'frame'] as $type)
        {
            $files = glob(dirname(__DIR__, 3) . '/public/images/profil/' . $type . '/thumbnail/*');
            foreach ($files === false ? [] : $files as $file)
            {
                if (is_file($file)) $versions[] = basename($file) . ':' . filemtime($file) . ':' . filesize($file);
            }
        }
        return substr(hash('sha256', implode('|', $versions)), 0, 16);
    }
}
