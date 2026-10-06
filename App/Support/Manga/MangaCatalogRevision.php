<?php

declare(strict_types=1);

namespace App\Support\Manga;

final class MangaCatalogRevision
{
    /** @param array<mixed> $catalog */
    public static function encode(array $catalog): string
    {
        if (!is_array($catalog['series'] ?? null) || !is_array($catalog['kinds'] ?? null))
            throw new \InvalidArgumentException('Invalid recommendation catalog.');
        unset($catalog['_revision']);
        $json = json_encode($catalog, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        // The revision and catalog are published together by one atomic replacement.
        return '{"_revision":"' . hash('sha256', $json) . '",' . substr($json, 1) . "\n";
    }

    public static function fingerprint(string $path): string|false
    {
        $file = @fopen($path, 'rb');
        if ($file === false) return false;
        try
        {
            $prefix = fread($file, 128);
            $stat = fstat($file);
            if ($prefix !== false && $stat !== false && preg_match('/^\{"_revision":"([a-f0-9]{64})",/', $prefix, $match) === 1)
            {
                // Detect ordinary manual edits too. Edits preserving metadata require republishing the revision.
                return $match[1] . ':' . hash('sha256', json_encode([$stat['size'], $stat['mtime'], $stat['ctime'], $stat['ino']], JSON_THROW_ON_ERROR));
            }
            // Legacy/unrecognized catalogs retain strict content fingerprinting until migrated.
            rewind($file);
            $hash = hash_init('sha256');
            $bytes = hash_update_stream($hash, $file);
            if ($stat === false || $bytes !== $stat['size']) return false;
            return hash_final($hash);
        }
        finally
        {
            fclose($file);
        }
    }
}
