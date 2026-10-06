<?php

declare(strict_types=1);

use Framework\Application\Bootstrap;

if (PHP_SAPI !== 'cli')
{ http_response_code(404); exit; }
if (array_diff(array_slice($argv, 1), ['--without-images']) !== [])
{
    fwrite(STDERR, "Usage: composer release:zip [-- --without-images]\n");
    exit(1);
}
$includeImages = !in_array('--without-images', array_slice($argv, 1), true);
define('ROOT', dirname(__DIR__, 2));

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
if (!preg_match('/^[a-zA-Z0-9][a-zA-Z0-9._-]*$/', $releaseName))
{
    fail('APP_NAME and APP_VERSION must form a safe archive filename (letters, digits, dots, underscores and hyphens).');
}
require __DIR__ . '/../Assets/build-assets.php';
$releasesDirectory = ROOT . DIRECTORY_SEPARATOR . 'releases';
$temporaryRoot = $releasesDirectory . DIRECTORY_SEPARATOR . '.build-temp-' . bin2hex(random_bytes(8));
$buildDirectory = $temporaryRoot . DIRECTORY_SEPARATOR . $releaseName;
$zipFile = $releasesDirectory . DIRECTORY_SEPARATOR . $releaseName . ($includeImages ? '' : '-without-images') . '.zip';

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

ensureDirectory($buildDirectory);
register_shutdown_function(static function () use ($temporaryRoot): void
{
    removeDirectory($temporaryRoot);
});

$directories = ['App', 'Config', 'Framework', 'scripts'];

foreach ($directories as $directory)
{
    copyDirectory(ROOT . DIRECTORY_SEPARATOR . $directory, $buildDirectory . DIRECTORY_SEPARATOR . $directory);
}

copyPublicDirectory(ROOT . DIRECTORY_SEPARATOR . 'public', $buildDirectory . DIRECTORY_SEPARATOR . 'public', $includeImages);

// Old bundles remain on the live server for open pages, but are not needed in a new delivery.
$assets = require $buildDirectory . '/Config/assets/versions.php';
foreach (array_keys($assets) as $path)
{
    if (!is_file($buildDirectory . '/public/' . $path)) unset($assets[$path]);
}
AtomicFile::writeIfChanged($buildDirectory . '/Config/assets/versions.php', "<?php\n\ndeclare(strict_types=1);\n\nreturn " . var_export($assets, true) . ";\n");

$environmentTemplate = (string) file_get_contents(ROOT . '/.env.example');
$environmentTemplate = preg_replace('/^(APP_ENV)=.*$/m', '$1=production', $environmentTemplate);
$environmentTemplate = preg_replace('/^(APP_DEBUG|PROFILER_ENABLED|SQL_TOOL_ENABLED|REGISTRATION_ENABLED)=.*$/m', '$1=false', (string) $environmentTemplate);

$rootFiles = ['composer.json', 'composer.lock', '.env.example'];

foreach ($rootFiles as $file)
{
    copyRequiredFile($file, $buildDirectory);
}

AtomicFile::writeIfChanged($buildDirectory . '/.env.example', (string) $environmentTemplate);

require_once __DIR__ . '/Support/ProductionDependencies.php';
ProductionDependencies::install($buildDirectory);

$optionalFiles = ['.htaccess'];

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
    'public/images'
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
echo 'Images included: ' . ($includeImages ? 'yes' : 'no (existing server images must be retained)') . PHP_EOL;
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

function copyPublicDirectory(string $source, string $destination, bool $includeImages): void
{
    $excluded = [normalizePath($source . '/js/dist')];
    if (!$includeImages) $excluded[] = normalizePath($source . '/images');
    copyDirectory($source, $destination, $excluded);
    $manifest = require ROOT . '/Config/assets/javascript-manifest.php';
    foreach ($manifest['files'] as $file)
    {
        if (!str_starts_with($file, 'js/dist/') || str_contains($file, '..')) fail('Invalid JavaScript manifest path.');
        copyFile($source . '/' . $file, $destination . '/' . $file);
    }
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
        new RecursiveDirectoryIterator($source, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($iterator as $item)
    {
        $sourcePath = $item->getPathname();
        if ($item->isLink()) fail('Symbolic links are not supported in releases: ' . $sourcePath);
        $normalizedSourcePath = normalizePath($sourcePath);

        foreach ($excludedPaths as $excludedPath)
        {
            if ($normalizedSourcePath === $excludedPath || str_starts_with($normalizedSourcePath, $excludedPath . '/'))
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

    copyFile($source, $destinationDirectory . DIRECTORY_SEPARATOR . basename($file));
}

function copyOptionalFile(string $file, string $destinationDirectory): void
{
    $source = ROOT . DIRECTORY_SEPARATOR . $file;

    if (! is_file($source))
    {
        return;
    }

    copyFile($source, $destinationDirectory . DIRECTORY_SEPARATOR . basename($file));
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
    $forbiddenPaths = ['.env', '.git', 'tests', 'releases'];

    foreach ($forbiddenPaths as $path)
    {
        $fullPath = $buildDirectory . DIRECTORY_SEPARATOR . $path;

        if (file_exists($fullPath))
        {
            fail('Forbidden artifact detected: ' . $path);
        }
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($buildDirectory, FilesystemIterator::SKIP_DOTS)
    );

    foreach ($iterator as $item)
    {
        if (! $item->isFile())
        {
            continue;
        }

        $extension = strtolower($item->getExtension());

        $relative = normalizePath(substr($item->getPathname(), strlen($buildDirectory) + 1));
        $migration = str_starts_with($relative, 'scripts/Database/migrations/') && $extension === 'sql';
        if (in_array($extension, ['log', 'sql', 'zip', 'rar', '7z'], true) && !$migration)
        {
            fail('Forbidden file detected: ' . $item->getPathname());
        }
    }
}

function createArchive(string $buildDirectory, string $zipFile): void
{
    require_once __DIR__ . '/Support/ReleaseArchive.php';
    ReleaseArchive::create($buildDirectory, $zipFile);
}
function removeDirectory(string $directory): void
{
    if (! is_dir($directory))
    {
        return;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS),
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

function normalizePath(string $path): string
{
    return str_replace('\\', '/', $path);
}

function fail(string $message): never
{
    fwrite(STDERR, PHP_EOL . '[FAILED] ' . $message . PHP_EOL);

    exit(1);
}
