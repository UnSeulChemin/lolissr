<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli')
{ http_response_code(404); exit; }
define('ROOT', dirname(__DIR__, 2));
require ROOT . '/vendor/autoload.php';
require ROOT . '/Framework/Support/Helpers.php';
require ROOT . '/scripts/Support/AtomicFile.php';
require ROOT . '/scripts/Manga/Support/MangacollecClient.php';
\Framework\Application\Bootstrap::loadEnvOnly();
$owner = filter_var($argv[1] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if ($owner === false) throw new RuntimeException('Specify the collection owner: composer manga:recommendations -- USER_ID');
$lock = fopen(ROOT . '/storage/manga-recommendations.lock', 'c');
if ($lock === false || !flock($lock, LOCK_EX | LOCK_NB)) throw new RuntimeException('Recommendation sync already running');
register_shutdown_function(static function () use ($lock): void
{ flock($lock, LOCK_UN); fclose($lock); });
$client = new MangacollecClient();
$series = $client->get('/series');
$kinds = $client->get('/kinds');
if (!is_array($series['series'] ?? null) || !is_array($kinds['kinds'] ?? null)) throw new RuntimeException('Incomplete recommendation catalog');
$path = ROOT . '/storage/manga-recommendations.json';
$previous = is_file($path) ? json_decode((string) file_get_contents($path), true) : [];
$catalog = ['series' => $series['series'], 'kinds' => $kinds['kinds'], 'covers' => is_array($previous['covers'] ?? null) ? $previous['covers'] : [], 'details' => is_array($previous['details'] ?? null) ? $previous['details'] : [], 'authors' => is_array($previous['authors'] ?? null) ? $previous['authors'] : []];
$db = new \Framework\Database\Database();
$collections = [];
$statement = $db->prepare('SELECT DISTINCT user_id, livre FROM manga WHERE user_id = :owner');
$statement->execute(['owner' => $owner]);
foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $row)
    $collections[$row['user_id']][] = $row['livre'];
$ownedTitles = [];
foreach ($collections as $titles)
    foreach ($titles as $title) $ownedTitles[\App\Services\Manga\MangaRecommendationService::normalize($title)] = true;
$authorIds = [];
foreach ($series['series'] as $entry)
{
    if (!isset($ownedTitles[\App\Services\Manga\MangaRecommendationService::normalize($entry['title'])])) continue;
    try
    {
        $detail = $client->get('/series/' . rawurlencode($entry['id']));
        foreach ($detail['tasks'] ?? [] as $task)
            if ($task['series_id'] === $entry['id']) $authorIds[$task['author_id']] = true;
    }
    catch (Throwable)
    { echo 'Author relations unavailable for ' . $entry['id'] . "\n"; }
}
foreach (array_keys($authorIds) as $authorId)
{
    try
    {
        $detail = $client->get('/authors/' . rawurlencode($authorId));
        foreach ($detail['authors'] ?? [] as $author)
        {
            if ($author['id'] !== $authorId) continue;
            $ids = [];
            foreach ($detail['tasks'] ?? [] as $task)
                if ($task['author_id'] === $authorId) $ids[] = $task['series_id'];
            $catalog['authors'][$authorId] = ['title' => trim(($author['first_name'] ?? '') . ' ' . $author['name']), 'series_ids' => array_values(array_unique($ids))];
        }
    }
    catch (Throwable)
    { echo 'Author bibliography unavailable for ' . $authorId . "\n"; }
}
$candidates = [];
foreach ($collections as $titles)
    foreach (['categories', 'authors'] as $mode)
    foreach (\App\Services\Manga\MangaRecommendationService::fromCatalog($catalog, $titles, $mode) as $recommendation)
        $candidates[$recommendation['id']] = $recommendation;
foreach ($candidates as $id => $recommendation)
{
    try
    {
        $detail = $client->get('/series/' . rawurlencode($id));
        $editions = array_values(array_filter($detail['editions'] ?? [], static fn ($edition) => $edition['series_id'] === $id));
        usort($editions, static fn ($a, $b) => [($a['parent_edition_id'] ?? null) !== null, $a['id']] <=> [($b['parent_edition_id'] ?? null) !== null, $b['id']]);
        foreach ($editions as $edition)
        {
            $volumes = $client->get('/editions/' . rawurlencode($edition['id']));
            $volumes = array_values(array_filter($volumes['volumes'] ?? [], static fn ($volume) => $volume['edition_id'] === $edition['id']));
            usort($volumes, static fn ($a, $b) => ($a['number'] ?? PHP_INT_MAX) <=> ($b['number'] ?? PHP_INT_MAX));
            foreach ($volumes as $volume)
            {
                $client->cover($volume['id'], $volume['image_url'] ?? null);
                if (!is_file(ROOT . '/public/images/manga/upcoming/' . $volume['id'] . '.jpg')) continue;
                $catalog['covers'][$id] = $volume['id'];
                $dates = array_filter(array_column($volumes, 'release_date'), static fn ($date) => is_string($date) && preg_match('/^\d{4}-\d{2}-\d{2}$/D', $date) === 1);
                sort($dates, SORT_STRING);
                $catalog['details'][$id] = ['edition' => $edition['title'] ?? 'Standard', 'volumeCount' => count($volumes), 'firstRelease' => $dates[0] ?? null];
                break 2;
            }
        }
        echo 'Cover ' . $id . ': ' . (isset($catalog['covers'][$id]) ? 'available' : 'unavailable') . "\n";
    }
    catch (Throwable)
    { echo 'Cover ' . $id . ": unavailable; suggestion retained.\n"; }
}
$catalog['updated_at'] = date(DATE_ATOM);
AtomicFile::writeIfChanged($path, json_encode($catalog, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR) . "\n", 0600);
echo 'Public recommendation catalog refreshed: ' . count($series['series']) . " series.\n";
