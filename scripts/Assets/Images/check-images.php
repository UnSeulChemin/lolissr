<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require dirname(__DIR__, 3) . '/vendor/autoload.php';

$root = dirname(__DIR__, 3);
$read = static function (string $path): ?array {
    if (!is_file($path)) return null;
    $data = json_decode((string) file_get_contents($path), true);
    return is_array($data) ? $data : null;
};
$issues = 0;
$report = static function (string $message) use (&$issues): void {
    $issues++;
    echo $message . PHP_EOL;
};
$ledger = $read($root . '/storage/thumbnail-optimization.json') ?? [];
$images = realpath($root . '/public/images');
if ($images === false) throw new RuntimeException('Missing images directory.');
$checked = 0;
foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($images, FilesystemIterator::SKIP_DOTS)) as $file)
{
    if (!$file->isFile() || $file->isLink()) continue;
    $path = $file->getRealPath();
    if ($path === false) continue;
    $extension = strtolower($file->getExtension());
    if (!in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true)) continue;
    $relative = str_replace('\\', '/', substr($path, strlen($images) + 1));
    $hash = hash_file('sha256', $path);
    if ($hash === false) throw new RuntimeException('Cannot read image: ' . $relative);
    $checked++;
    $stat = stat($path);
    if ($stat === false) throw new RuntimeException('Cannot stat image: ' . $relative);
    $signature = ['size' => $stat['size'], 'mtime' => $stat['mtime'], 'ctime' => $stat['ctime'], 'ino' => $stat['ino']];
    $fingerprint = $read($root . '/storage/image-fingerprints/' . hash('sha256', $path) . '.json');
    if (($fingerprint['format'] ?? null) !== 1 || ($fingerprint['signature'] ?? null) !== $signature || ($fingerprint['hash'] ?? null) !== $hash)
        $report('Fingerprint missing or outdated: ' . $relative);
    if (!in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true) || str_contains(basename($path), '.grid.')) continue;
    if (str_contains('/' . $relative, '/thumbnail/') && !str_contains($relative, '/optimized/'))
    {
        $edge = str_starts_with($relative, 'profil/banner/') ? 1440 : (str_starts_with($relative, 'profil/') ? 512 : 1200);
        if (($ledger[$relative] ?? null) !== [$hash, $edge . ':85:v1']) $report('Thumbnail candidate: ' . $relative);
    }
    if (preg_match('~^(manga|artbook|figurine|nendoroid|peluche)/thumbnail/[^/]+$~', $relative) === 1)
    {
        $target = preg_replace('/\.(jpg|jpeg|png|webp)$/i', '.grid.$1', $path);
        $state = $read($root . '/storage/grid-images/' . hash('sha256', $path) . '.json');
        if (($state['source'] ?? null) !== $hash || ($state['settings'] ?? null) !== '600:85:v1'
            || !is_bool($state['grid'] ?? null) || $state['grid'] !== is_file((string) $target))
            $report('Grid state missing or outdated: ' . $relative);
        elseif ($state['grid'] && @getimagesize((string) $target) === false)
            $report('Invalid grid image: ' . $relative);
    }
}
$versions = [];
foreach (['avatar' => 512, 'banner' => 2160, 'frame' => 512] as $type => $edge)
{
    foreach (glob($root . '/public/images/profil/' . $type . '/thumbnail/*') ?: [] as $path)
    {
        if (!is_file($path)) continue;
        $hash = hash_file('sha256', $path);
        if ($hash === false) throw new RuntimeException('Cannot read profile image.');
        $versions[] = $type . '/' . basename($path) . ':' . $hash;
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $size = @getimagesize($path);
        if (in_array($extension, ['png', 'webp'], true) && $size === false) $report('Invalid profile image: ' . $type . '/' . basename($path));
        elseif (($extension === 'png' && filesize($path) >= 200000) || ($extension === 'webp' && $size !== false && max($size[0], $size[1]) > $edge))
            $report('Profile conversion/resize candidate: ' . $type . '/' . basename($path));
    }
}
$profile = $read($root . '/storage/profile-images.json');
if (($profile['format'] ?? null) !== 2 || ($profile['version'] ?? null) !== hash('sha256', implode('|', $versions)))
    $report('Profile manifest missing or outdated.');

require $root . '/Framework/Support/Helpers.php';
if (!defined('ROOT')) define('ROOT', $root);
Framework\Application\Bootstrap::loadEnvOnly();
$database = new Framework\Database\Database();
foreach (['avatar', 'banner', 'frame'] as $type)
{
    $query = $database->prepare("SELECT COUNT(*) FROM users WHERE {$type} = ? AND {$type}_extension = 'png'");
    foreach (glob($root . '/public/images/profil/' . $type . '/thumbnail/*.webp') ?: [] as $path)
    {
        $query->execute([pathinfo($path, PATHINFO_FILENAME)]);
        if ((int) $query->fetchColumn() > 0) $report('Profile database extension to migrate: ' . $type . '/' . basename($path));
    }
}
echo "Images checked: {$checked}; items to review: {$issues}. No changes applied." . PHP_EOL;
if ($issues > 0) echo "Run composer images:build to process candidates and refresh metadata." . PHP_EOL;
