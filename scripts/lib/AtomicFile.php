<?php

declare(strict_types=1);

final class AtomicFile
{
    public static function writeIfChanged(string $path, string $contents): void
    {
        if (is_file($path) && file_get_contents($path) === $contents) return;
        $directory = realpath(dirname($path));
        if ($directory === false) throw new RuntimeException('Missing output directory: ' . dirname($path));
        $temporary = tempnam($directory, '.build-');
        if ($temporary === false)
            throw new RuntimeException('Cannot stage file in output directory: ' . $path);
        try
        {
            if (realpath(dirname($temporary)) !== $directory)
                throw new RuntimeException('Staging file is not in output directory: ' . $path);
            if (file_put_contents($temporary, $contents, LOCK_EX) !== strlen($contents))
                throw new RuntimeException('Incomplete staged write: ' . $path);
            $permissions = is_file($path) ? fileperms($path) : false;
            if (!chmod($temporary, $permissions === false ? 0644 : ($permissions & 0777)))
                throw new RuntimeException('Cannot preserve output permissions: ' . $path);
            // The previous file remains intact until this same-directory rename.
            // Never unlink it as a fallback if replacement fails.
            if (!@rename($temporary, $path)) throw new RuntimeException('Cannot replace output atomically: ' . $path);
            clearstatcache(true, $path);
        }
        finally
        {
            if (is_file($temporary)) unlink($temporary);
        }
    }
}
