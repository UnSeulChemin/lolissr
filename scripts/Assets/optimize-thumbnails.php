<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli')
{ http_response_code(404); exit; }
if (count($argv) > 2 || (isset($argv[1]) && $argv[1] !== '--apply'))
    throw new InvalidArgumentException('Usage: php scripts/Assets/optimize-thumbnails.php [--apply]');
require dirname(__DIR__, 2) . '/vendor/autoload.php';
require __DIR__ . '/../Support/BuildLock.php';
require __DIR__ . '/../Support/AtomicFile.php';
$root = dirname(__DIR__, 2);
BuildLock::acquire($root);
$apply = ($argv[1] ?? '') === '--apply';
$directory = realpath($root . '/public/images');
if ($directory === false) throw new RuntimeException('Missing images directory.');
$ledgerPath = $root . '/storage/thumbnail-optimization.json';
$ledger = is_file($ledgerPath) ? json_decode((string) file_get_contents($ledgerPath), true, 512, JSON_THROW_ON_ERROR) : [];
if (!is_array($ledger)) throw new RuntimeException('Invalid image optimization ledger.');
$backupManifestPath = $root . '/storage/backups/images/manifest.json';
$backups = is_file($backupManifestPath)
    ? json_decode((string) file_get_contents($backupManifestPath), true, 512, JSON_THROW_ON_ERROR) : [];
if (!is_array($backups)) throw new RuntimeException('Invalid image backup manifest.');
$saved = 0;
$count = 0;
$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS));
foreach ($files as $file)
{
    if (!$file->isFile() || $file->isLink()) continue;
    $path = $file->getRealPath();
    if ($path === false || !str_starts_with($path, $directory . DIRECTORY_SEPARATOR)) continue;
    $relative = str_replace('\\', '/', substr($path, strlen($directory) + 1));
    if (!str_contains('/' . $relative, '/thumbnail/') || str_contains($relative, '/optimized/')) continue;
    if (!in_array(strtolower($file->getExtension()), ['png', 'jpg', 'jpeg', 'webp'], true)) continue;
    $edge = str_starts_with($relative, 'profil/banner/') ? 1440 : (str_starts_with($relative, 'profil/') ? 512 : 1200);
    $settings = $edge . ':85:v1';
    $hash = hash_file('sha256', $path);
    if (($ledger[$relative] ?? null) === [$hash, $settings]) continue;
    if (!$apply)
    { echo 'Candidate: ' . $relative . PHP_EOL; continue; }
    $backup = $root . '/storage/backups/images/' . $hash . '.' . strtolower($file->getExtension());
    if (!is_dir(dirname($backup)) && !mkdir(dirname($backup), 0755, true) && !is_dir(dirname($backup)))
        throw new RuntimeException('Cannot create image backup directory.');
    if (!is_file($backup) && !copy($path, $backup)) throw new RuntimeException('Cannot back up ' . $relative);
    if (hash_file('sha256', $backup) !== $hash) throw new RuntimeException('Invalid image backup.');
    $backups[$relative][$hash] = basename($backup);
    AtomicFile::writeIfChanged($backupManifestPath, json_encode($backups, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . "\n");
    $before = filesize($path);
    if (App\Support\Media\ThumbnailOptimizer::optimize($path, $edge))
    {
        clearstatcache(true, $path);
        $saved += $before - filesize($path);
        $count++;
    }
    $ledger[$relative] = [hash_file('sha256', $path), $settings];
    // Save after every file so retries never repeatedly compress completed images.
    AtomicFile::writeIfChanged($ledgerPath, json_encode($ledger, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . "\n");
}
printf("Thumbnails: %d optimized, %.2f MB saved. Originals: storage/backups/images.\n", $count, $saved / 1048576);
if (!$apply) echo "Audit only; use --apply to optimize.\n";
