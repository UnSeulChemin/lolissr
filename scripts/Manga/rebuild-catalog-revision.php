<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli')
{ http_response_code(404); exit; }
define('ROOT', dirname(__DIR__, 2));
require ROOT . '/vendor/autoload.php';
require ROOT . '/scripts/Support/AtomicFile.php';
$lock = fopen(ROOT . '/storage/manga-recommendations.lock', 'c');
if ($lock === false || !flock($lock, LOCK_EX | LOCK_NB)) throw new RuntimeException('Recommendation sync already running');
try
{
    $path = ROOT . '/storage/manga-recommendations.json';
    $contents = file_get_contents($path);
    if ($contents === false) throw new RuntimeException('Recommendation catalog unavailable.');
    $catalog = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
    if (!is_array($catalog) || !is_array($catalog['series'] ?? null) || !is_array($catalog['kinds'] ?? null))
        throw new RuntimeException('Invalid recommendation catalog.');
    AtomicFile::writeIfChanged($path, \App\Support\Manga\MangaCatalogRevision::encode($catalog), 0600);
    echo "Recommendation catalog revision published (no network access).\n";
}
finally
{
    flock($lock, LOCK_UN);
    fclose($lock);
}
