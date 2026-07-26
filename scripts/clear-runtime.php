<?php

declare(strict_types=1);

const ALLOWED_RUNTIME_DIRECTORIES = [
    'cache' => 'storage/cache',
    'logs' => 'storage/logs',
    'sessions' => 'storage/sessions',
];

define('ROOT', dirname(__DIR__));

$target = strtolower(trim((string) ($argv[1] ?? '')));

if (! array_key_exists($target, ALLOWED_RUNTIME_DIRECTORIES))
{
    fail(
        'Unknown runtime directory. Allowed values: '
        . implode(', ', array_keys(ALLOWED_RUNTIME_DIRECTORIES))
    );
}

$relativeDirectory = ALLOWED_RUNTIME_DIRECTORIES[$target];
$directory = ROOT . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativeDirectory);

if (! is_dir($directory))
{
    fail('Runtime directory not found: ' . $relativeDirectory);
}

$deletedFiles = clearDirectory($directory);

echo PHP_EOL;
echo '[OK] ' . ucfirst($target) . ' cleared.';
echo PHP_EOL;
echo '[INFO] Deleted files: ' . $deletedFiles;
echo PHP_EOL;

exit(0);

function clearDirectory(string $directory): int
{
    $entries = scandir($directory);

    if ($entries === false)
    {
        fail('Unable to read directory: ' . $directory);
    }

    $deletedFiles = 0;

    foreach ($entries as $entry)
    {
        if ($entry === '.' || $entry === '..' || $entry === '.gitkeep')
        {
            continue;
        }

        $path = $directory . DIRECTORY_SEPARATOR . $entry;

        if (is_dir($path) && ! is_link($path))
        {
            removeDirectory($path);
            $deletedFiles++;

            continue;
        }

        if (! unlink($path))
        {
            fail('Unable to remove file: ' . $path);
        }

        $deletedFiles++;
    }

    return $deletedFiles;
}

function removeDirectory(string $directory): void
{
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(
            $directory,
            FilesystemIterator::SKIP_DOTS
        ),
        RecursiveIteratorIterator::CHILD_FIRST
    );

    foreach ($iterator as $item)
    {
        $path = $item->getPathname();

        if ($item->isDir() && ! $item->isLink())
        {
            if (! rmdir($path))
            {
                fail('Unable to remove directory: ' . $path);
            }

            continue;
        }

        if (! unlink($path))
        {
            fail('Unable to remove file: ' . $path);
        }
    }

    if (! rmdir($directory))
    {
        fail('Unable to remove directory: ' . $directory);
    }
}

function fail(string $message): never
{
    fwrite(STDERR, PHP_EOL . '[FAILED] ' . $message . PHP_EOL);

    exit(1);
}