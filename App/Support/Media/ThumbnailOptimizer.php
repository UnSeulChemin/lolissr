<?php

declare(strict_types=1);

namespace App\Support\Media;

final class ThumbnailOptimizer
{
    public static function createGrid(string $path): bool
    {
        $target = preg_replace('/\.(jpg|jpeg|png|webp)$/i', '.grid.$1', $path);
        if (!is_string($target) || $target === $path || str_contains(basename($path), '.grid.')) return false;
        $sourceHash = hash_file('sha256', $path);
        if ($sourceHash === false) throw new \RuntimeException('Cannot fingerprint grid source.');
        $resolved = realpath($path);
        $statePath = dirname(__DIR__, 3) . '/storage/grid-images/' . hash('sha256', $resolved === false ? $path : $resolved) . '.json';
        $state = is_file($statePath) ? json_decode((string) file_get_contents($statePath), true) : null;
        if (is_array($state) && ($state['source'] ?? null) === $sourceHash && ($state['settings'] ?? null) === '600:85:v1'
            && (($state['grid'] ?? null) === true ? is_file($target) : !is_file($target))) return false;
        $temporary = dirname($path) . '/.grid-' . bin2hex(random_bytes(16));
        try
        {
            if (!copy($path, $temporary)) throw new \RuntimeException('Cannot stage grid image.');
            if (!self::optimize($temporary, 600))
            {
                if (is_file($target) && !unlink($target)) throw new \RuntimeException('Cannot remove obsolete grid image.');
                ImageManifest::write($statePath, ['source' => $sourceHash, 'settings' => '600:85:v1', 'grid' => false]);
                return false;
            }
            if (!rename($temporary, $target)) throw new \RuntimeException('Cannot publish grid image.');
            ImageManifest::write($statePath, ['source' => $sourceHash, 'settings' => '600:85:v1', 'grid' => true]);
            return true;
        }
        finally
        {
            if (is_file($temporary)) unlink($temporary);
        }
    }

    public static function forgetGrid(string $path): void
    {
        $resolved = realpath($path);
        $statePath = dirname(__DIR__, 3) . '/storage/grid-images/' . hash('sha256', $resolved === false ? $path : $resolved) . '.json';
        if (is_file($statePath) && !unlink($statePath)) throw new \RuntimeException('Cannot remove grid state.');
    }

    /** Preserve the format, transparency and animated files. Publish only smaller output. */
    public static function optimize(string $path, int $maxEdge = 1200, int $quality = 85): bool
    {
        if ($maxEdge < 1 || $quality < 1 || $quality > 100)
            throw new \InvalidArgumentException('Invalid thumbnail settings.');
        $bytes = file_get_contents($path);
        if ($bytes === false) throw new \RuntimeException('Cannot read thumbnail.');
        $info = getimagesizefromstring($bytes);
        if ($info === false) throw new \RuntimeException('Invalid thumbnail.');
        if (!in_array($info[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP], true)) return false;
        $offset = $info[2] === IMAGETYPE_PNG ? 8 : 12;
        if ($info[2] !== IMAGETYPE_JPEG)
        {
            while ($offset + 12 <= strlen($bytes))
            {
                $png = $info[2] === IMAGETYPE_PNG;
                $type = substr($bytes, $offset + ($png ? 4 : 0), 4);
                if ($type === 'acTL' || $type === 'ANIM') return false;
                $chunk = unpack($png ? 'Nlength' : 'Vlength', substr($bytes, $offset + ($png ? 0 : 4), 4));
                if ($chunk === false) throw new \RuntimeException('Invalid image chunk.');
                $length = (int) $chunk['length'];
                $offset += $length + ($png ? 12 : 8 + $length % 2);
            }
        }
        $orientation = $info[2] === IMAGETYPE_JPEG ? JpegOrientation::read($bytes) : 1;
        if ($orientation === null) return false;
        // Leave large validated originals intact when GD cannot safely fit in the request budget.
        $memoryLimit = ini_get('memory_limit');
        if (preg_match('/^\s*(\d+)\s*([KMG]?)\s*$/i', $memoryLimit, $limit) === 1)
        {
            $multiplier = match (strtoupper($limit[2]))
            {
                'K' => 1024,
                'M' => 1048576,
                'G' => 1073741824,
                default => 1,
            };
            $required = $info[0] * $info[1] * 8 + strlen($bytes) * 2 + 8388608;
            if ($required + memory_get_usage(true) > (int) $limit[1] * $multiplier) return false;
        }
        $image = imagecreatefromstring($bytes);
        if ($image === false) throw new \RuntimeException('Cannot decode thumbnail.');
        $temporary = null;
        try
        {
            imagepalettetotruecolor($image);
            imagesavealpha($image, true);
            if (in_array($orientation, [2, 4, 5, 7], true))
                imageflip($image, in_array($orientation, [4, 5], true) ? IMG_FLIP_VERTICAL : IMG_FLIP_HORIZONTAL);
            $angle = match ($orientation)
            { 3 => 180, 5, 6, 7 => -90, 8 => 90, default => 0 };
            if ($angle !== 0)
            {
                $rotated = imagerotate($image, $angle, 0);
                if ($rotated === false) throw new \RuntimeException('Cannot orient thumbnail.');
                imagedestroy($image);
                $image = $rotated;
            }
            $info[0] = imagesx($image);
            $info[1] = imagesy($image);
            $scale = min(1, $maxEdge / max($info[0], $info[1]));
            if ($scale < 1)
            {
                $resized = imagecreatetruecolor(max(1, (int) round($info[0] * $scale)), max(1, (int) round($info[1] * $scale)));
                imagealphablending($resized, false);
                imagesavealpha($resized, true);
                $transparent = imagecolorallocatealpha($resized, 0, 0, 0, 127);
                if ($transparent === false) throw new \RuntimeException('Cannot allocate thumbnail transparency.');
                imagefill($resized, 0, 0, $transparent);
                imagecopyresampled($resized, $image, 0, 0, 0, 0, imagesx($resized), imagesy($resized), $info[0], $info[1]);
                imagedestroy($image);
                $image = $resized;
            }
            $temporary = dirname($path) . DIRECTORY_SEPARATOR . '.image-' . bin2hex(random_bytes(16));
            $reservation = fopen($temporary, 'x+b');
            if ($reservation === false) throw new \RuntimeException('Cannot stage thumbnail.');
            fclose($reservation);
            $saved = match ($info[2])
            {
                IMAGETYPE_JPEG => imagejpeg($image, $temporary, $quality),
                IMAGETYPE_PNG => imagepng($image, $temporary, 6),
                IMAGETYPE_WEBP => imagewebp($image, $temporary, $quality),
            };
            if (!$saved) throw new \RuntimeException('Cannot encode thumbnail.');
            $output = getimagesize($temporary);
            if ($output === false || $output[0] !== imagesx($image) || $output[1] !== imagesy($image) || $output[2] !== $info[2])
                throw new \RuntimeException('Invalid optimized thumbnail.');
            if (filesize($temporary) >= strlen($bytes)) return false;
            if (!rename($temporary, $path)) throw new \RuntimeException('Cannot publish thumbnail.');
            return true;
        }
        finally
        {
            imagedestroy($image);
            if (is_string($temporary) && is_file($temporary)) unlink($temporary);
        }
    }
}
