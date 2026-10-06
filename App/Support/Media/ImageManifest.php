<?php
declare(strict_types=1);
namespace App\Support\Media;

final class ImageManifest
{
    /** @param array<string, mixed> $data */
    public static function write(string $path, array $data): void
    {
        $directory = dirname($path);
        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory))
            throw new \RuntimeException('Cannot create image manifest directory.');
        $temporary = $path . '.' . bin2hex(random_bytes(16));
        $json = json_encode($data, JSON_THROW_ON_ERROR);
        try
        {
            if (file_put_contents($temporary, $json, LOCK_EX) !== strlen($json)
                || !rename($temporary, $path)) throw new \RuntimeException('Cannot publish image manifest.');
        }
        finally
        {
            if (is_file($temporary)) unlink($temporary);
        }
    }
}
