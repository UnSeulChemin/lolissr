<?php

declare(strict_types=1);

$manifestPath = tempnam(sys_get_temp_dir(), 'lolissr-js-manifest-');
if ($manifestPath === false) throw new RuntimeException('Cannot create test payload');
try
{
    file_put_contents($manifestPath, json_encode(require dirname(__DIR__, 2) . '/Config/assets/javascript-manifest.php', JSON_THROW_ON_ERROR));
    $argv[1] ??= 'http://localhost/lolissr';
    $argv[2] = __DIR__ . '/javascript-bundle-browser.js';
    $argv[3] = $manifestPath;
    require __DIR__ . '/run-browser-scenario.php';
}
finally
{
    if (is_file($manifestPath)) unlink($manifestPath);
}
