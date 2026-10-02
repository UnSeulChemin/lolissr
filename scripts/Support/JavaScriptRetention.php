<?php

declare(strict_types=1);

require_once __DIR__ . '/AtomicFile.php';

final class JavaScriptRetention
{
    public const DAYS = 7;

    /** @param list<string> $active Files relative to public/. */
    public static function prune(string $root, array $active, ?int $now = null): int
    {
        $now ??= time();
        $directory = realpath($root . '/public/js/dist');
        if ($directory === false) throw new RuntimeException('Missing bundle directory.');
        $ledger = $root . '/storage/javascript-retention.json';
        $previous = is_file($ledger)
            ? json_decode((string) file_get_contents($ledger), true, 512, JSON_THROW_ON_ERROR)
            : [];
        if (!is_array($previous)) throw new RuntimeException('Invalid bundle retention ledger.');
        $retired = [];
        $removed = 0;
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS));
        foreach ($files as $file)
        {
            if (!$file->isFile() || $file->isLink()) continue;
            $relative = str_replace('\\', '/', substr($file->getPathname(), strlen($directory) + 1));
            if (!preg_match('~^(?:chunks/)?[a-zA-Z0-9_-]+-[A-Z0-9]{8}\.js$~D', $relative)) continue;
            $path = 'js/dist/' . $relative;
            if (in_array($path, $active, true)) continue;
            // Called on the deployed server only; local build timestamps are ignored.
            $since = $previous[$path] ?? $now;
            if (!is_int($since)) throw new RuntimeException('Invalid bundle retirement timestamp.');
            if ($now - $since >= self::DAYS * 86400)
            {
                if (!unlink($file->getPathname())) throw new RuntimeException('Cannot prune bundle: ' . $path);
                $removed++;
            }
            else
            {
                $retired[$path] = $since;
            }
        }
        ksort($retired);
        AtomicFile::writeIfChanged($ledger, json_encode((object) $retired, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . "\n");
        return $removed;
    }
}
