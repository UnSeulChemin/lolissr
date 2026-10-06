<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$file = $argv[1] ?? '';
$withImages = ($argv[2] ?? '') !== '--without-images';
$zip = new ZipArchive();
if ($zip->open($file, ZipArchive::CHECKCONS) !== true) throw new RuntimeException('Cannot read release ZIP.');
try
{
    foreach (['public/index.php', 'App/Support/Helpers.php', 'Framework/Application/Bootstrap.php',
        'vendor/autoload.php', '.htaccess', '.env.example', 'Config/assets.php'] as $required)
        if ($zip->locateName($required) === false) throw new RuntimeException('Missing: ' . $required);
    $files = [];
    for ($i = 0; $i < $zip->numFiles; $i++)
    {
        $name = (string) $zip->getNameIndex($i);
        if ($name === '.env' || str_starts_with($name, '.git/') || str_starts_with($name, 'tests/')
            || str_starts_with($name, 'releases/') || str_starts_with($name, 'vendor/phpstan/'))
            throw new RuntimeException('Forbidden release entry: ' . $name);
        if (str_starts_with($name, 'js/dist/')) throw new RuntimeException('Wrong asset root.');
        if (str_starts_with($name, 'public/js/dist/') && str_ends_with($name, '.js')) $files[] = substr($name, 7);
        if (str_starts_with($name, 'storage/') && !str_ends_with($name, '/') && !str_ends_with($name, '/.gitkeep'))
            throw new RuntimeException('Local runtime data included: ' . $name);
    }
    $manifest = require $root . '/Config/javascript.php';
    $expected = $manifest['files'];
    sort($files); sort($expected);
    if ($files !== $expected) throw new RuntimeException('Inactive or missing JavaScript chunks.');
    $environment = (string) $zip->getFromName('.env.example');
    foreach (['APP_ENV=production', 'APP_DEBUG=false', 'PROFILER_ENABLED=false', 'SQL_TOOL_ENABLED=false', 'REGISTRATION_ENABLED=false'] as $setting)
        if (!str_contains($environment, $setting)) throw new RuntimeException('Missing production setting: ' . $setting);
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root . '/public/images', FilesystemIterator::SKIP_DOTS)) as $image)
    {
        if (!$image->isFile() || $image->getFilename() === '.gitkeep') continue;
        $name = str_replace('\\', '/', substr($image->getPathname(), strlen($root) + 1));
        $contents = $zip->getFromName($name);
        if ($withImages && ($contents === false || hash('sha256', $contents) !== hash_file('sha256', $image->getPathname())))
            throw new RuntimeException('Missing/modified image: ' . $name);
        if (!$withImages && $contents !== false) throw new RuntimeException('Images included in update ZIP.');
    }
    $versions = (string) $zip->getFromName('Config/assets.php');
    $assets = require $root . '/Config/assets.php';
    foreach ($assets as $path => $hash)
    {
        $contents = $zip->getFromName('public/' . $path);
        if ($contents === false)
        {
            if (str_contains($versions, "'" . $path . "'")) throw new RuntimeException('Manifest retains omitted asset: ' . $path);
            continue;
        }
        if (hash('sha256', $contents) !== $hash || !str_contains($versions, "'" . $path . "' => '" . $hash . "'"))
            throw new RuntimeException('Asset hash mismatch: ' . $path);
    }
    echo "PASS: release entry point, production vendor/template, active bundles, manifest hashes, image policy and no local runtime data.\n";
}
finally
{ $zip->close(); }
