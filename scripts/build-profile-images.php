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
$root = dirname(__DIR__) . '/public/images/profil';
$before = 0;
$after = 0;
$count = 0;
$replacements = [];
foreach (['avatar', 'banner', 'frame'] as $type)
{
    foreach (glob($root . '/' . $type . '/thumbnail/*.png') ?: [] as $source)
    {
        if (filesize($source) < 200000) continue;
        $bytes = file_get_contents($source);
        if ($bytes === false) throw new RuntimeException('Cannot read ' . $source);
        // Parse PNG chunks: an acTL chunk marks APNG, which GD would flatten.
        $animated = false;
        for ($offset = 8; $offset + 12 <= strlen($bytes);)
        {
            $length = unpack('Nlength', substr($bytes, $offset, 4));
            if ($length === false) throw new RuntimeException('Invalid PNG');
            if (substr($bytes, $offset + 4, 4) === 'acTL') $animated = true;
            $offset += 12 + $length['length'];
        }
        $directory = dirname($source);
        $legacy = $directory . '/optimized/' . basename($source) . '.webp';
        $target = $directory . '/' . pathinfo($source, PATHINFO_FILENAME) . '.webp';
        if ($animated)
        {
            continue;
        }
        // Do not overwrite an independently supplied WebP with the same name.
        if (is_file($target)) throw new RuntimeException('PNG/WebP name collision: ' . $target);
        if (is_file($legacy) && filemtime($legacy) >= filemtime($source))
        {
            if (!copy($legacy, $target)) throw new RuntimeException('Cannot copy ' . $legacy);
        }
        else
        {
            $image = imagecreatefrompng($source);
            if ($image === false) throw new RuntimeException('Cannot decode ' . $source);
            imagepalettetotruecolor($image);
            imagesavealpha($image, true);
            if (!imagewebp($image, $target, IMG_WEBP_LOSSLESS)) throw new RuntimeException('Cannot encode ' . $source);
            imagedestroy($image);
        }
        if (filesize($target) >= filesize($source))
        {
            unlink($target);
            continue;
        }
        $sourceInfo = getimagesize($source);
        $targetInfo = getimagesize($target);
        if ($sourceInfo === false || $targetInfo === false || $targetInfo[2] !== IMAGETYPE_WEBP
            || $sourceInfo[0] !== $targetInfo[0] || $sourceInfo[1] !== $targetInfo[1])
        {
            throw new RuntimeException('Invalid converted image: ' . $target);
        }
        $replacements[] = [$source, $legacy];
        $before += filesize($source);
        $after += filesize($target);
        $count++;
    }
}

require dirname(__DIR__) . '/vendor/autoload.php';
require dirname(__DIR__) . '/Framework/Support/Helpers.php';
if (!defined('ROOT')) define('ROOT', dirname(__DIR__));
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
printf("Profile images: %d PNGs replaced, %d -> %d bytes (%.1f%% saved).\n", $count, $before, $after, $before > 0 ? 100 * (1 - $after / $before) : 0);
