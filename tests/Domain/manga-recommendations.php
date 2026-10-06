<?php

declare(strict_types=1);

require dirname(__DIR__, 2) . '/tests/Support/bootstrap.php';

use App\Repositories\Manga\MangaRepository;
use App\Services\Manga\MangaRecommendationService;

use Framework\Database\Database;

$db = (new ReflectionClass(Database::class))->newInstanceWithoutConstructor();
(new ReflectionMethod(PDO::class, '__construct'))->invoke($db, 'sqlite::memory:');
$db->exec('CREATE TABLE manga (user_id INT, slug TEXT, livre TEXT, numero INT)');
$db->exec("INSERT INTO manga VALUES (1, 'owned', 'Étoile', 1), (1, 'owned', 'Étoile', 2), (2, 'foreign', 'Foreign', 1)");
$path = tempnam(sys_get_temp_dir(), 'recommendations-');
$hidden = tempnam(sys_get_temp_dir(), 'hidden-recommendations-');
$service = new MangaRecommendationService(new MangaRepository($db), $path, $hidden);
$assert = static function (bool $condition, string $message): void
{ if (!$condition) throw new RuntimeException($message); };
$id = static fn (int $n): string => sprintf('00000000-0000-0000-0000-%012d', $n);
try
{
    $series = [];
    foreach (['Etoile', 'Suggestion', 'Foreign', 'Adult'] as $i => $title)
        $series[] = ['id' => $id($i), 'title' => $title, 'adult_content' => $i === 3];
    file_put_contents($path, json_encode(['series' => $series, 'kinds' => [
        ['title' => 'Aventure', 'series_ids' => [$id(0), $id(1), $id(3)]],
        ['title' => 'Autre', 'series_ids' => [$id(2)]]
    ]], JSON_THROW_ON_ERROR));
    $result = $service->all();
    $assert(count($result) === 1 && $result[0]['title'] === 'Suggestion', 'Owned/adult series or another owner influenced suggestions');
    $assert(str_contains($result[0]['reason'], 'Aventure'), 'Suggestion lost its explanation');
    $assert($result[0]['score'] === 1 && $result[0]['categories'][0]['points'] === 1, 'Multiple owned volumes inflated category points');
    $assert($service->hide('invalid')->status === 422, 'Invalid hidden UUID accepted');
    $assert($service->hide($id(1))->success && $service->all() === [], 'Hidden suggestion remained visible');
    $assert((new MangaRecommendationService(new MangaRepository($db), $path, $hidden))->hidden() === [$id(1)], 'Hidden preferences did not persist');
    file_put_contents($hidden, '[]');
    $authorCatalog = ['series' => $series, 'kinds' => [], 'authors' => [
        ['title' => 'Auteur partagé', 'series_ids' => [$id(0), $id(1)]],
        ['title' => 'Autre auteur', 'series_ids' => [$id(2), $id(3)]]
    ]];
    $authorResult = MangaRecommendationService::fromCatalog($authorCatalog, ['Etoile'], 'authors');
    $assert(count($authorResult) === 1 && $authorResult[0]['id'] === $id(1) && str_contains($authorResult[0]['reason'], 'Auteur partagé'), 'Author recommendations used unrelated authors or categories');
    $manySeries = $series;
    foreach (['categories', 'authors'] as $mode)
    {
        $confirmedResult = MangaRecommendationService::fromCatalog(
            ['series' => $series, 'kinds' => $authorCatalog['authors'], 'authors' => $authorCatalog['authors']],
            ['Different local edition name'], $mode, [], [$id(0)]
        );
        $assert(count($confirmedResult) === 1 && $confirmedResult[0]['id'] === $id(1), 'Confirmed identity failed to exclude a renamed owned series or score its relations');
    }
    $ambiguousCatalog = ['series' => [
        ['id' => $id(0), 'title' => 'Same title'], ['id' => $id(1), 'title' => 'Same title'],
        ['id' => $id(2), 'title' => 'Unrelated suggestion']
    ], 'kinds' => [['title' => 'Aventure', 'series_ids' => [$id(0), $id(2)]]]];
    $assert(MangaRecommendationService::fromCatalog($ambiguousCatalog, ['Same title']) === [], 'Ambiguous titles influenced recommendation scores');
    $confirmedMapping = MangaRecommendationService::confirmedSeriesIds(
        ['edition_series' => ['0ed3326d-c9f1-462a-b21c-74aa8cdd9456' => $id(0)]],
        [['slug' => 'to-love-trouble-official-data-book', 'livre' => 'Local title']], 1
    );
    $assert($confirmedMapping === [$id(0)], 'Configured edition identity was not resolved');
    $assert(MangaRecommendationService::confirmedSeriesIds(['edition_series' => ['0ed3326d-c9f1-462a-b21c-74aa8cdd9456' => $id(0)]], [], 1) === [], 'Removed owned series remained excluded by a cached identity');
    $manyIds = [$id(0)];
    for ($i = 10; $i < 80; $i++)
    { $manySeries[] = ['id' => $id($i), 'title' => 'Suggestion ' . $i]; $manyIds[] = $id($i); }
    $manyCatalog = ['series' => $manySeries, 'kinds' => [['title' => 'Aventure', 'series_ids' => $manyIds]]];
    $assert(count(MangaRecommendationService::fromCatalog($manyCatalog, ['Etoile'])) === 40, 'Recommendations were not capped at 40');
    $assert(!in_array($id(10), array_column(MangaRecommendationService::fromCatalog($manyCatalog, ['Etoile'], 'categories', [$id(10)]), 'id'), true), 'Hidden IDs were not excluded before ranking');
    $fiveHidden = array_slice(array_column(MangaRecommendationService::fromCatalog($manyCatalog, ['Etoile']), 'id'), 0, 5);
    $replacements = MangaRecommendationService::fromCatalog($manyCatalog, ['Etoile'], 'categories', $fiveHidden);
    $assert(count($replacements) === 40 && array_intersect($fiveHidden, array_column($replacements, 'id')) === [], 'Five hidden suggestions were not replaced before the limit');
    $assert(MangaRecommendationService::hiddenForOwner(1, $hidden) === [], 'CLI and page preferences differ');
    $GLOBALS['testCurrentUser'] = null;
    $assert($service->all() === [], 'Anonymous recommendations leaked');
    unset($GLOBALS['testCurrentUser']);
    file_put_contents($path, '{');
    $assert($service->all() === [], 'Corrupt catalog broke recommendations');
}
finally
{
    unset($GLOBALS['testCurrentUser']);
    unlink($path);
    unlink($hidden);
    if (is_file($hidden . '.lock')) unlink($hidden . '.lock');
}
echo "PASS: recommendations use current owner, exclude owned/adult series, explain categories and tolerate corrupt catalogs.\n";
