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
$favorites = tempnam(sys_get_temp_dir(), 'favorite-recommendations-');
$service = new MangaRecommendationService(new MangaRepository($db), $path, $hidden, $favorites);
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
    $assert($service->searchFilters('aventure')['categories'][0]['url'] === 'manga/series/recommandations/categorie/aventure', 'Category search lost its filter link');
    $assert($service->searchFilters('missing')['categories'] === [], 'Unrelated categories matched search');
    $fiveKinds = [];
    for ($n = 1; $n <= 6; $n++) $fiveKinds[] = ['title' => 'Category ' . $n, 'series_ids' => [$id(0), $id(1)]];
    $topFive = MangaRecommendationService::fromCatalog(['series' => $series, 'kinds' => $fiveKinds], ['Etoile']);
    $assert(count($topFive[0]['categories']) === 6, 'Recommendation metadata dropped categories beyond the fifth badge');
    $assert($result[0]['score'] === 1 && $result[0]['categories'][0]['points'] === 1, 'Multiple owned volumes inflated category points');
    $assert($service->hide('invalid')->status === 422, 'Invalid hidden UUID accepted');
    $assert($service->setFavorite('invalid', true)->status === 422, 'Invalid favorite accepted');
    $assert($service->setFavorite($id(99), true)->status === 404, 'Unknown favorite accepted');
    $assert($service->setFavorite($id(1), true)->success && $service->setFavorite($id(1), true)->success && count($service->favorites()) === 1, 'Favorites were not saved idempotently');
    $updatedCatalog = json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
    $updatedCatalog['details'][$id(1)] = ['volumeCount' => 7, 'edition' => 'Updated edition', 'firstRelease' => '2026-10-07'];
    file_put_contents($path, json_encode($updatedCatalog, JSON_THROW_ON_ERROR));
    $updatedFavorite = $service->favorites()[0];
    $assert($updatedFavorite['volumeCount'] === 7 && $updatedFavorite['edition'] === 'Updated edition' && $updatedFavorite['firstRelease'] === '07/10/2026', 'Saved favorites kept obsolete catalog metadata');
    $changedCatalog = $updatedCatalog;
    $changedCatalog['series'][1]['title'] = 'Updated suggestion';
    $changedCatalog['kinds'][] = ['title' => 'Romance', 'series_ids' => [$id(0), $id(1)]];
    file_put_contents($path, json_encode($changedCatalog, JSON_THROW_ON_ERROR));
    $freshFavorite = $service->favorites()[0];
    $assert($freshFavorite['title'] === 'Updated suggestion' && $freshFavorite['score'] === 2 && count($freshFavorite['categories']) === 2, 'Favorite title, score and categories did not follow the current catalog');
    file_put_contents($path, json_encode($updatedCatalog, JSON_THROW_ON_ERROR));
    $assert($service->hide($id(1))->success && $service->all() === [], 'Hidden suggestion remained visible');
    $assert(MangaRecommendationService::favoriteIdsForOwner(1, $favorites) === [$id(1)], 'Sync dropped a favorite absent from current recommendations');
    $assert(MangaRecommendationService::favoriteIdsForOwner(0, $favorites) === [], 'Sync accepted an invalid owner');
    $assert(count($service->favorites()) === 1, 'Hiding a suggestion removed its favorite');
    $assert((new MangaRecommendationService(new MangaRepository($db), $path, $hidden))->hidden() === [$id(1)], 'Hidden preferences did not persist');
    $assert($service->hiddenSuggestions()[0]['title'] === 'Suggestion', 'Hidden list lost its catalog title');
    $assert($service->hide($id(1), false)->success && $service->hidden() === [] && count($service->all()) === 1, 'Restore did not return the suggestion to recommendations');
    $assert(count($service->favorites()) === 1, 'Restore changed favorites');
    file_put_contents($hidden, '[]');
    $authorCatalog = ['series' => $series, 'kinds' => [], 'authors' => [
        ['title' => 'Auteur partagé', 'series_ids' => [$id(0), $id(1)]],
        ['title' => 'Autre auteur', 'series_ids' => [$id(2), $id(3)]]
    ]];
    $authorResult = MangaRecommendationService::fromCatalog($authorCatalog, ['Etoile'], 'authors');
    file_put_contents($path, json_encode($authorCatalog, JSON_THROW_ON_ERROR));
    $authorFilters = $service->searchFilters('auteur');
    $assert(count($authorFilters['authors']) === 1 && str_starts_with($authorFilters['authors'][0]['url'], 'manga/series/recommandations-auteurs/auteur/'), 'Author search included an unrelated author or lost its link');
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
    $targeted = MangaRecommendationService::fromCatalog($manyCatalog, ['Etoile'], 'categories', [], [], [$id(79)]);
    $assert(count($targeted) === 1 && $targeted[0]['id'] === $id(79) && $targeted[0]['score'] === 1, 'A favorite outside the top 40 lost its current score');
    $assert(!in_array($id(10), array_column(MangaRecommendationService::fromCatalog($manyCatalog, ['Etoile'], 'categories', [$id(10)]), 'id'), true), 'Hidden IDs were not excluded before ranking');
    $fiveHidden = array_slice(array_column(MangaRecommendationService::fromCatalog($manyCatalog, ['Etoile']), 'id'), 0, 5);
    $replacements = MangaRecommendationService::fromCatalog($manyCatalog, ['Etoile'], 'categories', $fiveHidden);
    $assert(count($replacements) === 40 && array_intersect($fiveHidden, array_column($replacements, 'id')) === [], 'Five hidden suggestions were not replaced before the limit');
    $assert(MangaRecommendationService::hiddenForOwner(1, $hidden) === [], 'CLI and page preferences differ');
    $GLOBALS['testCurrentUser'] = null;
    $assert($service->favorites() === [] && $service->setFavorite($id(1), false)->status === 401, 'Anonymous favorite access accepted');
    $assert($service->all() === [], 'Anonymous recommendations leaked');
    unset($GLOBALS['testCurrentUser']);
    file_put_contents($path, '{');
    $assert(count($service->favorites()) === 1 && $service->favorites()[0]['title'] === 'Suggestion', 'Catalog refresh lost saved favorite data');
    $assert($service->setFavorite($id(1), false)->success && $service->favorites() === [], 'Favorite removal failed');
    $assert($service->all() === [], 'Corrupt catalog broke recommendations');
}
finally
{
    unset($GLOBALS['testCurrentUser']);
    unlink($path);
    unlink($hidden);
    unlink($favorites);
    if (is_file($favorites . '.lock')) unlink($favorites . '.lock');
    if (is_file($hidden . '.lock')) unlink($hidden . '.lock');
}
echo "PASS: recommendations use current owner, exclude owned/adult series, explain categories and tolerate corrupt catalogs.\n";
