<?php

declare(strict_types=1);

use Framework\Application\Bootstrap;
use Framework\Application\BootstrapCache;

require dirname(__DIR__, 2) . '/vendor/autoload.php';
define('ROOT', dirname(__DIR__, 2));
require ROOT . '/Framework/Support/Helpers.php';
require ROOT . '/scripts/Support/AtomicFile.php';

$action = $argv[1] ?? 'build';
if (! in_array($action, ['build', 'clear'], true))
{
    fwrite(STDERR, "Usage: php scripts/Maintenance/bootstrap-cache.php [build|clear]\n");
    exit(1);
}

$path = BootstrapCache::path();
if ($action === 'clear')
{
    if (is_file($path) && ! unlink($path)) throw new RuntimeException('Cannot clear bootstrap cache.');
}
else
{
    Bootstrap::loadEnvOnly();
    $contents = BootstrapCache::compile();
    $directory = dirname($path);
    if (! is_dir($directory) && ! mkdir($directory, 0700, true) && ! is_dir($directory))
        throw new RuntimeException('Cannot create bootstrap cache directory.');
    AtomicFile::writeIfChanged($path, $contents, 0600);
}
if (function_exists('opcache_invalidate')) opcache_invalidate($path, true);
echo 'Bootstrap cache ' . ($action === 'clear' ? 'cleared' : 'built') . ".\n";
