<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli')
{ http_response_code(404); exit; }
if (count($argv) > 2 || (isset($argv[1]) && $argv[1] !== '--apply'))
    throw new InvalidArgumentException('Usage: php scripts/Assets/Images/build-grid-images.php [--apply]');
require_once dirname(__DIR__, 3) . '/vendor/autoload.php';
require_once __DIR__ . '/../../Support/BuildLock.php';
BuildLock::acquire(dirname(__DIR__, 3));
(static function (bool $apply): void
{
    $created = 0;
    foreach (['manga', 'artbook', 'figurine', 'nendoroid', 'peluche'] as $type)
    {
        foreach (glob(dirname(__DIR__, 3) . '/public/images/' . $type . '/thumbnail/*') ?: [] as $path)
        {
            if (!is_file($path) || is_link($path) || str_contains(basename($path), '.grid.')) continue;
            if (!in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'webp'], true)) continue;
            if (!$apply) continue;
            if (\App\Support\Media\ThumbnailOptimizer::createGrid($path)) $created++;
        }
    }
    echo 'Grid images created: ' . $created . ($apply ? '' : ' (audit only; use --apply)') . PHP_EOL;
})(($argv[1] ?? '') === '--apply');
if (($argv[1] ?? '') === '--apply') require_once __DIR__ . '/build-image-fingerprints.php';
