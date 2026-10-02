<?php

declare(strict_types=1);

require dirname(__DIR__, 2) . '/tests/Support/bootstrap.php';
\Framework\Application\Bootstrap::loadEnvOnly();
$root = dirname(__DIR__, 2) . '/public/images/profil';
$count = 0;
foreach (['avatar', 'banner', 'frame'] as $type)
{
    $directory = $root . '/' . $type . '/thumbnail';
    if (is_dir($directory . '/optimized')) throw new RuntimeException('Obsolete optimized directory remains');
    foreach (glob($directory . '/*.webp') ?: [] as $file)
    {
        $image = imagecreatefromwebp($file);
        if ($image === false) throw new RuntimeException('Invalid WebP: ' . $file);
        if (max(imagesx($image), imagesy($image)) > ($type === 'banner' ? 2160 : 512))
            throw new RuntimeException('Oversized profile image: ' . $file);
        imagedestroy($image);
        if (is_file(substr($file, 0, -5) . '.png')) throw new RuntimeException('Duplicate PNG remains');
        $count++;
    }
}
if ($count === 0) throw new RuntimeException('No converted profile images');
$database = new \Framework\Database\Database();
$database->exec('SET SESSION TRANSACTION READ ONLY');
foreach (['avatar', 'banner', 'frame'] as $type)
{
    $rows = $database->query("SELECT DISTINCT {$type} AS name, {$type}_extension AS extension FROM users");
    foreach ($rows as $row)
    {
        if (!is_file($root . '/' . $type . '/thumbnail/' . $row->name . '.' . $row->extension))
            throw new RuntimeException('Missing image referenced by a user: ' . $type);
    }
}
echo "PASS: $count valid WebP images, no duplicate PNGs or optimized directories, all stored user images exist.\n";
