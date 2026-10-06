<?php

declare(strict_types=1);

require dirname(__DIR__, 3) . '/scripts/Release/Support/ProductionDependencies.php';
$root = dirname(__DIR__, 3);
$stage = $root . '/storage/tools/release-deps-test-' . bin2hex(random_bytes(8));
mkdir($stage . '/Framework/Support', 0755, true);
try
{
    foreach (['composer.json', 'composer.lock'] as $file) copy($root . '/' . $file, $stage . '/' . $file);
    copy($root . '/Framework/Support/Strings.php', $stage . '/Framework/Support/Strings.php');
    ProductionDependencies::install($stage);
    $installed = json_decode((string) file_get_contents($stage . '/vendor/composer/installed.json'), true, 512, JSON_THROW_ON_ERROR);
    if ($installed['dev'] !== false || is_dir($stage . '/vendor/phpstan')) throw new RuntimeException('Development dependencies in release.');
    $loader = require $stage . '/vendor/autoload.php';
    if (!$loader->findFile('Framework\\Support\\Strings')) throw new RuntimeException('Production PSR-4 autoload failed.');
    $loader->unregister();
    if (!is_file($root . '/vendor/phpstan/phpstan/phpstan.phar')) throw new RuntimeException('Development vendor was modified.');
    echo "PASS: production dependencies, PSR-4 autoload and unchanged development vendor.\n";
}
finally
{
    // The directory was uniquely created by this test, under the workspace tools directory.
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($stage, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
    foreach ($iterator as $item) $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
    rmdir($stage);
}
