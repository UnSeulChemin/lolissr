<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli')
{
    http_response_code(404);
    exit;
}

// Replace large static PNGs with lossless WebP, then migrate stored extensions.
// Animated PNGs are excluded.
if (!extension_loaded('gd') || !function_exists('imagewebp') || !defined('IMG_WEBP_LOSSLESS'))
{
    throw new RuntimeException('GD with lossless WebP support is required.');
}
require __DIR__ . '/../Support/ProfileImageBuilder.php';
$root = dirname(__DIR__, 2) . '/public/images/profil';
$before = 0;
$after = 0;
$count = 0;
$replacements = [];
foreach (['avatar' => 512, 'banner' => 2160, 'frame' => 512] as $type => $maxEdge)
{
    $directory = $root . '/' . $type . '/thumbnail';
    foreach (glob($directory . '/*.png') ?: [] as $source)
    {
        if (filesize($source) < 200000) continue;
        $target = $directory . '/' . pathinfo($source, PATHINFO_FILENAME) . '.webp';
        if (!ProfileImageBuilder::prepare($source, $target, $maxEdge)) continue;
        $replacements[] = [$source, $directory . '/optimized/' . basename($source) . '.webp'];
        $before += filesize($source);
        $after += filesize($target);
        $count++;
    }
    foreach (glob($directory . '/*.webp') ?: [] as $file)
    {
        $size = filesize($file);
        if (!ProfileImageBuilder::prepare($file, $file, $maxEdge)) continue;
        clearstatcache(true, $file);
        if (filesize($file) === $size) continue;
        $before += $size;
        $after += filesize($file);
        $count++;
    }
}
require dirname(__DIR__, 2) . '/vendor/autoload.php';
require dirname(__DIR__, 2) . '/Framework/Support/Helpers.php';
if (!defined('ROOT')) define('ROOT', dirname(__DIR__, 2));
\Framework\Application\Bootstrap::loadEnvOnly();
$database = new \Framework\Database\Database();
$database->transaction(static function () use ($database, $root): void {
    foreach (['avatar', 'banner', 'frame'] as $type)
    {
        $update = $database->prepare("UPDATE users SET {$type}_extension = 'webp' WHERE {$type} = ? AND {$type}_extension = 'png'");
        foreach (glob($root . '/' . $type . '/thumbnail/*.webp') ?: [] as $file)
        {
            $update->execute([pathinfo($file, PATHINFO_FILENAME)]);
        }
    }
});
// Remove sources only after all replacements are validated and DB paths migrated.
foreach ($replacements as [$source, $legacy])
{
    if (!unlink($source)) throw new RuntimeException('Cannot remove ' . $source);
    if (is_file($legacy) && !unlink($legacy)) throw new RuntimeException('Cannot remove ' . $legacy);
}
foreach (['avatar', 'banner', 'frame'] as $type)
{
    $directory = $root . '/' . $type . '/thumbnail/optimized';
    // Nonrecursive removal: never delete unexpected files in the directory.
    if (is_dir($directory) && count(scandir($directory) ?: []) === 2) rmdir($directory);
}
printf("Profile images: %d images optimized, %d -> %d bytes (%.1f%% saved).\n", $count, $before, $after, $before > 0 ? 100 * (1 - $after / $before) : 0);
