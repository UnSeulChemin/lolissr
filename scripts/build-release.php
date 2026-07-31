<?php

declare(strict_types=1);

use Framework\Application\Bootstrap;

use ZipArchive;

define('ROOT', dirname(__DIR__));

require ROOT . '/vendor/autoload.php';
require ROOT . '/Framework/Support/Helpers.php';

Bootstrap::loadEnvOnly();

$projectName = trim((string) env('APP_NAME'));
$version = trim((string) env('APP_VERSION'));

if ($projectName === '')
{
    fail('APP_NAME is required.');
}

if ($version === '')
{
    fail('APP_VERSION is required.');
}

if (! class_exists(ZipArchive::class))
{
    fail('The PHP Zip extension is required.');
}

$releaseName = $projectName . '_v' . $version;
$releasesDirectory = ROOT . DIRECTORY_SEPARATOR . 'releases';
$temporaryRoot = $releasesDirectory . DIRECTORY_SEPARATOR . '.build-temp';
$buildDirectory = $temporaryRoot . DIRECTORY_SEPARATOR . $releaseName;
$zipFile = $releasesDirectory . DIRECTORY_SEPARATOR . $releaseName . '.zip';

echo PHP_EOL;
echo '============================================================' . PHP_EOL;
echo PHP_EOL;
echo '              >> LOLISSR ADVENTURER GUILD <<' . PHP_EOL;
echo PHP_EOL;
echo '                   Quest : Build Release' . PHP_EOL;
echo PHP_EOL;
echo '============================================================' . PHP_EOL;
echo PHP_EOL;

echo 'Application : ' . $projectName . PHP_EOL;
echo 'Version     : ' . $version . PHP_EOL;
echo 'Archive     : ' . $zipFile . PHP_EOL;
echo PHP_EOL;

ensureDirectory($releasesDirectory);

removeDirectory($temporaryRoot);
removeFile($zipFile);

ensureDirectory($buildDirectory);

$directories = [
    'App',
    'Config',
    'Framework',
    'scripts',
    'vendor',
];

foreach ($directories as $directory)
{
    copyDirectory(
        ROOT . DIRECTORY_SEPARATOR . $directory,
        $buildDirectory . DIRECTORY_SEPARATOR . $directory
    );
}

copyPublicDirectory(
    ROOT . DIRECTORY_SEPARATOR . 'public',
    $buildDirectory . DIRECTORY_SEPARATOR . 'public'
);

$rootFiles = [
    'composer.json',
    'composer.lock',
    '.env.example',
];

foreach ($rootFiles as $file)
{
    copyRequiredFile($file, $buildDirectory);
}

$optionalFiles = [
    '.htaccess',
    'README.md',
];

foreach ($optionalFiles as $file)
{
    copyOptionalFile($file, $buildDirectory);
}

$runtimeDirectories = [
    'storage',
    'storage/cache',
    'storage/logs',
    'storage/sessions',
    'storage/backups',
    'storage/backups/database',
    'public/images',
];

foreach ($runtimeDirectories as $directory)
{
    $destination = $buildDirectory
        . DIRECTORY_SEPARATOR
        . str_replace('/', DIRECTORY_SEPARATOR, $directory);

    ensureDirectory($destination);

    $sourceGitkeep = ROOT
        . DIRECTORY_SEPARATOR
        . str_replace('/', DIRECTORY_SEPARATOR, $directory)
        . DIRECTORY_SEPARATOR
        . '.gitkeep';

    if (is_file($sourceGitkeep))
    {
        copyFile($sourceGitkeep, $destination . DIRECTORY_SEPARATOR . '.gitkeep');
    }
}

verifyRelease($buildDirectory);
createArchive($buildDirectory, $zipFile);

removeDirectory($temporaryRoot);

echo PHP_EOL;
echo '============================================================' . PHP_EOL;
echo PHP_EOL;
echo '                 QUEST COMPLETED' . PHP_EOL;
echo PHP_EOL;
echo '       The release artifact has been forged.' . PHP_EOL;
echo '       Sensitive files have been excluded.' . PHP_EOL;
echo PHP_EOL;
echo '       Archive:' . PHP_EOL;
echo '       ' . $zipFile . PHP_EOL;
echo PHP_EOL;
echo '============================================================' . PHP_EOL;
echo PHP_EOL;

exit(0);

function copyPublicDirectory(string $source, string $destination): void
{
    copyDirectory(
        $source,
        $destination,
        [
            normalizePath($source . DIRECTORY_SEPARATOR . 'images'),
        ]
    );
}

/**
 * @param list<string> $excludedPaths
 */
function copyDirectory(string $source, string $destination, array $excludedPaths = []): void
{
    if (! is_dir($source))
    {
        fail('Missing directory: ' . $source);
    }

    ensureDirectory($destination);

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(
            $source,
            FilesystemIterator::SKIP_DOTS
        ),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($iterator as $item)
    {
        $sourcePath = $item->getPathname();
        $normalizedSourcePath = normalizePath($sourcePath);

        foreach ($excludedPaths as $excludedPath)
        {
            if (
                $normalizedSourcePath === $excludedPath
                || str_starts_with($normalizedSourcePath, $excludedPath . '/')
            )
            {
                continue 2;
            }
        }

        $relativePath = substr($sourcePath, strlen($source) + 1);
        $destinationPath = $destination . DIRECTORY_SEPARATOR . $relativePath;

        if ($item->isDir())
        {
            ensureDirectory($destinationPath);

            continue;
        }

        copyFile($sourcePath, $destinationPath);
    }
}

function copyRequiredFile(string $file, string $destinationDirectory): void
{
    $source = ROOT . DIRECTORY_SEPARATOR . $file;

    if (! is_file($source))
    {
        fail('Missing file: ' . $file);
    }

    copyFile(
        $source,
        $destinationDirectory . DIRECTORY_SEPARATOR . basename($file)
    );
}

function copyOptionalFile(string $file, string $destinationDirectory): void
{
    $source = ROOT . DIRECTORY_SEPARATOR . $file;

    if (! is_file($source))
    {
        return;
    }

    copyFile(
        $source,
        $destinationDirectory . DIRECTORY_SEPARATOR . basename($file)
    );
}

function copyFile(string $source, string $destination): void
{
    ensureDirectory(dirname($destination));

    if (! copy($source, $destination))
    {
        fail('Unable to copy file: ' . $source);
    }
}

function ensureDirectory(string $directory): void
{
    if (is_dir($directory))
    {
        return;
    }

    if (! mkdir($directory, 0755, true) && ! is_dir($directory))
    {
        fail('Unable to create directory: ' . $directory);
    }
}

function verifyRelease(string $buildDirectory): void
{
    $forbiddenPaths = [
        '.env',
        '.git',
        'tests',
        'releases',
    ];

    foreach ($forbiddenPaths as $path)
    {
        $fullPath = $buildDirectory . DIRECTORY_SEPARATOR . $path;

        if (file_exists($fullPath))
        {
            fail('Forbidden artifact detected: ' . $path);
        }
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(
            $buildDirectory,
            FilesystemIterator::SKIP_DOTS
        )
    );

    foreach ($iterator as $item)
    {
        if (! $item->isFile())
        {
            continue;
        }

        $extension = strtolower($item->getExtension());

        if (in_array($extension, ['log', 'sql', 'zip', 'rar', '7z'], true))
        {
            fail('Forbidden file detected: ' . $item->getPathname());
        }
    }
}

function createArchive(string $buildDirectory, string $zipFile): void
{
    $zip = new ZipArchive();

    if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true)
    {
        fail('Unable to create release archive.');
    }

    try
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                $buildDirectory,
                FilesystemIterator::SKIP_DOTS
            ),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item)
        {
            $path = $item->getPathname();
            $relativePath = str_replace(
                DIRECTORY_SEPARATOR,
                '/',
                substr($path, strlen($buildDirectory) + 1)
            );

            if ($item->isDir())
            {
                $zip->addEmptyDir($relativePath);

                continue;
            }

            if (! $zip->addFile($path, $relativePath))
            {
                fail('Unable to add file to archive: ' . $relativePath);
            }
        }
    }
    finally
    {
        $zip->close();
    }

    clearstatcache(true, $zipFile);

    if (! is_file($zipFile) || filesize($zipFile) === false || filesize($zipFile) <= 0)
    {
        fail('The release archive was not created correctly.');
    }
}

function removeDirectory(string $directory): void
{
    if (! is_dir($directory))
    {
        return;
    }

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

function removeFile(string $file): void
{
    if (is_file($file) && ! unlink($file))
    {
        fail('Unable to remove file: ' . $file);
    }
}

function normalizePath(string $path): string
{
    return str_replace('\\', '/', $path);
}

function fail(string $message): never
{
    fwrite(STDERR, PHP_EOL . '[FAILED] ' . $message . PHP_EOL);

    exit(1);
}