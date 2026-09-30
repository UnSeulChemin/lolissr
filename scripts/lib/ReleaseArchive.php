<?php

declare(strict_types=1);

final class ReleaseArchive
{
    public static function create(string $source, string $destination): void
    {
        $temporary = tempnam(dirname($destination), '.release-');
        if ($temporary === false) throw new RuntimeException('Cannot stage release archive.');
        try
        {
            if (realpath(dirname($temporary)) !== realpath(dirname($destination)))
                throw new RuntimeException('Release must be staged beside its destination.');
            $zip = new ZipArchive();
            if ($zip->open($temporary, ZipArchive::OVERWRITE) !== true)
                throw new RuntimeException('Cannot open staged release archive.');
            try
            {
                $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);
                foreach ($iterator as $item)
                {
                    $name = str_replace(DIRECTORY_SEPARATOR, '/', substr($item->getPathname(), strlen($source) + 1));
                    $added = $item->isDir() ? $zip->addEmptyDir($name) : $zip->addFile($item->getPathname(), $name);
                    if (!$added) throw new RuntimeException('Cannot add release entry: ' . $name);
                }
            }
            finally
            {
                if (!$zip->close()) throw new RuntimeException('Cannot finalize release archive.');
            }
            $verify = new ZipArchive();
            if ($verify->open($temporary, ZipArchive::CHECKCONS) !== true)
                throw new RuntimeException('Release archive verification failed.');
            $count = $verify->numFiles;
            $verify->close();
            if ($count === 0) throw new RuntimeException('Release archive is empty.');
            // Never unlink the existing release if replacement fails.
            if (!@rename($temporary, $destination)) throw new RuntimeException('Cannot replace release archive. Existing archive preserved.');
        }
        finally
        {
            if (is_file($temporary)) unlink($temporary);
        }
    }
}
