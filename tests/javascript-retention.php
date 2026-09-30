<?php

declare(strict_types=1);

require dirname(__DIR__) . '/scripts/lib/JavaScriptRetention.php';
$root = sys_get_temp_dir() . '/bundle-retention-' . bin2hex(random_bytes(8));
mkdir($root . '/public/js/dist/chunks', 0755, true);
mkdir($root . '/Config');
mkdir($root . '/storage');
$active = 'js/dist/app-AAAAAAAA.js';
$old = 'js/dist/chunks/chunk-BBBBBBBB.js';
$shared = 'js/dist/chunks/chunk-CCCCCCCC.js';
$files = [$active, $old, $shared, 'js/dist/.htaccess'];
try
{
    // A ledger shipped from a much older local build must not shorten server retention.
    file_put_contents($root . '/Config/javascript-retention.json', json_encode([$old => -700000]));
    foreach ($files as $file) file_put_contents($root . '/public/' . $file, 'fixture');
    $check = static function (bool $ok): void { if (!$ok) throw new RuntimeException('Bundle retention regression.'); };
    $check(JavaScriptRetention::prune($root, [$active, $shared], 1000) === 0);
    $check(JavaScriptRetention::prune($root, [$active, $shared], 1000 + 6 * 86400) === 0);
    $check(JavaScriptRetention::prune($root, [$active, $shared], 1000 + 7 * 86400) === 1);
    $check(!is_file($root . '/public/' . $old));
    $check(is_file($root . '/public/' . $shared));
    $check(JavaScriptRetention::prune($root, [$active], 1000 + 8 * 86400) === 0);
    $check(JavaScriptRetention::prune($root, [$active, $shared], 1000 + 14 * 86400) === 0);
    $check(JavaScriptRetention::prune($root, [$active], 1000 + 15 * 86400) === 0);
    $check(is_file($root . '/public/js/dist/.htaccess'));
    echo "PASS: retirement grace period, shared active chunks, reactivation and non-bundle files.\n";
}
finally
{
    unlink($root . '/Config/javascript-retention.json');
    foreach ($files as $file) if (is_file($root . '/public/' . $file)) unlink($root . '/public/' . $file);
    if (is_file($root . '/storage/javascript-retention.json')) unlink($root . '/storage/javascript-retention.json');
    foreach (['public/js/dist/chunks', 'public/js/dist', 'public/js', 'public', 'Config', 'storage', ''] as $dir) rmdir($root . '/' . $dir);
}
