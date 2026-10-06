<?php
declare(strict_types=1);

$project = dirname(__DIR__, 3);
define('ROOT', sys_get_temp_dir() . '/manga-cache-' . bin2hex(random_bytes(8)));
require $project . '/vendor/autoload.php';
require $project . '/Framework/Support/Helpers.php';

use App\Models\User\User;
use App\Repositories\Manga\MangaRepository;
use App\Services\Manga\MangaRecommendationService;

use Framework\Config\Config;
use Framework\Database\Database;

function user(): ?User
{ return $GLOBALS['cacheTestOwner']; }
$owner = new User();
$owner->id = 1;
$GLOBALS['cacheTestOwner'] = $owner;
mkdir(ROOT . '/storage/cache', 0700, true);
mkdir(ROOT . '/Config/settings', 0700, true);
file_put_contents(ROOT . '/Config/settings/manga-releases.php', "<?php return ['editions' => []];");
Config::prime(['cache' => ['enabled' => true, 'ttl' => 3600], 'app' => ['base_uri' => '/test/']]);
$db = (new ReflectionClass(Database::class))->newInstanceWithoutConstructor();
(new ReflectionMethod(PDO::class, '__construct'))->invoke($db, 'sqlite::memory:');
final class RecommendationCacheStatement extends PDOStatement
{
    public static int $collectionReads = 0;
    protected function __construct()
    {}
    public function execute(?array $params = null): bool
    {
        if (str_starts_with($this->queryString, 'SELECT slug, livre, numero FROM')) self::$collectionReads++;
        return parent::execute($params);
    }
}
$db->setAttribute(PDO::ATTR_STATEMENT_CLASS, [RecommendationCacheStatement::class]);
$db->exec("CREATE TABLE manga (user_id INT, slug TEXT, livre TEXT, numero INT, id INTEGER PRIMARY KEY, editeur TEXT, statut TEXT DEFAULT 'en_cours')");
$db->exec("INSERT INTO manga (user_id, slug, livre, numero) VALUES (1, 'owned', 'Owned', 1), (2, 'suggestion', 'Suggestion', 1)");
$id = static fn (int $n): string => sprintf('00000000-0000-0000-0000-%012d', $n);
$catalog = ['series' => [['id' => $id(0), 'title' => 'Owned'], ['id' => $id(1), 'title' => 'Suggestion'], ['id' => $id(2), 'title' => 'Second owned']],
    'kinds' => [['title' => 'Action', 'series_ids' => [$id(0), $id(1), $id(2)]]],
    'authors' => [['title' => 'Auteur', 'series_ids' => [$id(0), $id(1), $id(2)]]],
    'details' => [$id(1) => ['firstRelease' => '2026-10-07', 'volumeCount' => 2, 'edition' => 'Standard']]];
$write = static function () use (&$catalog): void
{ file_put_contents(ROOT . '/storage/manga-recommendations.json', json_encode($catalog, JSON_THROW_ON_ERROR)); };
$service = static fn (): MangaRecommendationService => new MangaRecommendationService(new MangaRepository($db));
$check = static function (bool $condition, string $message): void
{ if (!$condition) throw new RuntimeException($message); };
try
{
    Config::prime(['cache' => ['enabled' => true, 'ttl' => 3600], 'app' => ['base_uri' => '/test/', 'profiler' => true]]);
    \Framework\Debug\Profiler::startRequest();
    $repository = new MangaRepository($db);
    $check($repository->seriesForCreate() === [['slug' => 'owned', 'livre' => 'Owned', 'editeur' => null, 'statut' => 'en_cours', 'next_numero' => 2]], 'Instrumented series read changed associative results');
    $check($repository->releaseCollection() === [['slug' => 'owned', 'livre' => 'Owned', 'numero' => 1]], 'Instrumented collection read changed associative results');
    $check($repository->ownedNumbers('owned') === [1], 'Instrumented owned numbers read changed column results');
    $counters = (new ReflectionProperty(\Framework\Debug\Profiler::class, 'counters'))->getValue();
    $durations = (new ReflectionProperty(\Framework\Debug\Profiler::class, 'durations'))->getValue();
    $check(($counters['database.query.count'] ?? 0) === 3 && ($durations['database.query'] ?? 0) > 0, 'Manga reads were not counted and timed');
    Config::prime(['cache' => ['enabled' => true, 'ttl' => 3600], 'app' => ['base_uri' => '/test/']]);
    \Framework\Debug\Profiler::startRequest();
    $write();
    $benchmark = static function (bool $cold) use ($service): float
    {
        $samples = [];
        for ($sample = 0; $sample < 21; $sample++)
        {
            if ($cold)
            {
                foreach (['manga.recommendations.v3.1.categories', 'manga.recommendations.v3.1.authors', 'manga.recommendation-filters.v1.1'] as $key)
                    \Framework\Cache\Cache::forget($key);
            }
            $start = hrtime(true);
            $service()->searchFilters('a');
            if ($sample > 0) $samples[] = (hrtime(true) - $start) / 1_000_000;
        }
        sort($samples);
        return ($samples[9] + $samples[10]) / 2;
    };
    if (in_array('--profile', $argv, true))
        printf("Filters benchmark (small SQLite fixture, 20 samples): cold %.3f ms, warm %.3f ms\n", $benchmark(true), $benchmark(false));
    foreach (['manga.recommendations.v3.1.categories', 'manga.recommendations.v3.1.authors', 'manga.recommendation-filters.v1.1'] as $key)
        \Framework\Cache\Cache::forget($key);
    $check(count($service()->all()) === 2, 'Initial recommendations missing');
    $check($service()->setFavorite($id(1), true)->success, 'Cannot save fixture favorite');
    $check($service()->favorites()[0]['firstRelease'] === '07/10/2026', 'Initial favorite date missing');
    $files = glob(ROOT . '/storage/cache/*.cache');
    $check(count($files) === 2, 'Persistent recommendations and favorites cache not created');
    $snapshots = array_map('file_get_contents', $files);
    $service()->all();
    $service()->favorites();
    $check(array_map('file_get_contents', $files) === $snapshots, 'Warm cache was rewritten unnecessarily');

    $filterCache = ROOT . '/storage/cache/' . sha1('manga.recommendation-filters.v1.1') . '.cache';
    RecommendationCacheStatement::$collectionReads = 0;
    $filters = $service()->searchFilters('a');
    $check(RecommendationCacheStatement::$collectionReads === 1, 'Cold filters must read the collection once');
    $check(array_column($filters['categories'], 'title') === ['Action'] && array_column($filters['authors'], 'title') === ['Auteur'], 'Filter modes changed');
    $check($filters['categories'][0]['url'] === 'manga/series/recommandations/categorie/action'
        && $filters['authors'][0]['url'] === 'manga/series/recommandations-auteurs/auteur/auteur', 'Filter URLs changed');
    $filterSnapshot = file_get_contents($filterCache);
    RecommendationCacheStatement::$collectionReads = 0;
    $check($service()->searchFilters('ACTION')['authors'] === [], 'Query filtering must remain independent of cached titles');
    $check(RecommendationCacheStatement::$collectionReads === 1 && file_get_contents($filterCache) === $filterSnapshot, 'Warm filters must reuse normalized titles and read the collection once');
    RecommendationCacheStatement::$collectionReads = 0;
    $check($service()->searchFilters('!!!') === ['categories' => [], 'authors' => []]
        && RecommendationCacheStatement::$collectionReads === 0, 'Empty normalized query should skip collection reads');

    foreach (['storage/manga-releases.json' => '{"users":{}}', 'Config/settings/manga-releases.php' => "<?php return ['editions' => [], 'test_revision' => 1];"] as $relative => $contents)
    {
        file_put_contents(ROOT . '/' . $relative, $contents);
        $check($service()->searchFilters('a') === $filters, 'Release configuration changed unrelated filter results');
        $check(file_get_contents($filterCache) !== $filterSnapshot, 'Release/configuration revision did not invalidate filter cache');
        $filterSnapshot = file_get_contents($filterCache);
    }
    Config::prime(['cache' => ['enabled' => true, 'ttl' => 3600], 'app' => ['base_uri' => '/other/']]);
    $check($service()->searchFilters('a') === $filters && file_get_contents($filterCache) !== $filterSnapshot, 'Base URI revision did not invalidate filter cache');
    Config::prime(['cache' => ['enabled' => true, 'ttl' => 3600], 'app' => ['base_uri' => '/test/']]);

    $catalog['series'][1]['title'] = 'Updated suggestion';
    $catalog['kinds'][0]['title'] = 'Aventure';
    $catalog['details'][$id(1)]['firstRelease'] = null;
    $write();
    $fresh = $service()->favorites()[0];
    $check($fresh['title'] === 'Updated suggestion' && $fresh['firstRelease'] === null, 'Catalog revision did not replace cached favorite title and removed date');
    $check(in_array('Updated suggestion', array_column($service()->all(), 'title'), true), 'Catalog revision did not replace recommendations');
    $check($service()->searchFilters('action')['categories'] === [] && array_column($service()->searchFilters('aventure')['categories'], 'title') === ['Aventure'], 'Catalog revision did not replace filter titles');

    $db->exec("INSERT INTO manga (user_id, slug, livre, numero) VALUES (1, 'second-owned', 'Second owned', 1)");
    $check($service()->all()[0]['score'] === 2 && $service()->favorites()[0]['score'] === 2, 'Collection revision did not replace cached scores');
    $filterSnapshot = file_get_contents($filterCache);
    $service()->searchFilters('a');
    $check(file_get_contents($filterCache) !== $filterSnapshot, 'Collection revision did not invalidate filter cache');
    $check($service()->hide($id(1))->success && $service()->all() === [], 'Hidden revision did not replace cached recommendations');
    $check($service()->searchFilters('a') === ['categories' => [], 'authors' => []], 'Collection/hidden revisions did not replace cached filters');
    $check($service()->favorites()[0]['score'] === 2, 'Hiding changed favorite score');
    $check(count(glob(ROOT . '/storage/cache/*.cache')) === 3, 'Revisions accumulated cache entries');

    $owner->id = 2;
    $db->exec("UPDATE manga SET livre = 'Updated suggestion' WHERE user_id = 2");
    $check($service()->favorites() === [] && !in_array($id(1), array_column($service()->all(), 'id'), true), 'Owner cache isolation failed');
    $check($service()->searchFilters('aventure')['categories'] !== [] && is_file(ROOT . '/storage/cache/' . sha1('manga.recommendation-filters.v1.2') . '.cache'), 'Filter owner isolation failed');
    $owner->id = 1;
    file_put_contents(ROOT . '/storage/manga-recommendations-hidden-1.json', '[]');
    $catalog['kinds'] = [];
    foreach (['Épopée A', 'Alpha 10', 'Alpha 2', 'Alpha 1', 'Aventure', 'Action', 'Art', 'Action'] as $title)
        $catalog['kinds'][] = ['title' => $title, 'series_ids' => [$id(0), $id(1), $id(2)]];
    $write();
    $expectedTitles = array_values(array_unique(array_column($catalog['kinds'], 'title')));
    sort($expectedTitles, SORT_NATURAL | SORT_FLAG_CASE);
    $check(array_column($service()->searchFilters('a')['categories'], 'title') === array_slice($expectedTitles, 0, 5), 'Filter ordering, deduplication or five-result limit changed');
    $check(array_column($service()->searchFilters('epopee')['categories'], 'title') === ['Épopée A'], 'Accented filter matching changed');
    $custom = new MangaRecommendationService(new MangaRepository($db), ROOT . '/storage/manga-recommendations.json');
    $check($custom->searchFilters('epopee') === $service()->searchFilters('epopee'), 'Custom catalog results differ');
    $catalog['kinds'][0]['title'] = 'Autre';
    $write();
    $check($custom->searchFilters('epopee')['categories'] === [], 'Custom catalog snapshot leaked across operations');
    Config::prime(['cache' => ['enabled' => false], 'app' => ['base_uri' => '/test/']]);
    $check($service()->searchFilters('epopee')['categories'] === [], 'Disabled cache served stale filters');
    $hiddenFile = ROOT . '/storage/manga-recommendations-hidden-1.json';
    file_put_contents($hiddenFile, '{invalid');
    $check($service()->hide($id(1))->success && $service()->hide($id(1))->success
        && $service()->hidden() === [$id(1)], 'Hidden JSON recovery or duplicate handling changed');
    $check($service()->hide($id(1), false)->success && $service()->hidden() === [], 'Unhiding did not publish an empty list');
    $favoriteFile = ROOT . '/storage/manga-recommendations-favorites-1.json';
    file_put_contents($favoriteFile, '{invalid');
    $check($service()->setFavorite($id(1), true)->success && $service()->setFavorite($id(1), true)->success
        && count($service()->favorites()) === 1, 'Favorite JSON recovery or duplicate handling changed');
    $check($service()->setFavorite($id(1), false)->success && $service()->favorites() === [], 'Favorite removal changed');
    $blocked = ROOT . '/storage/blocked.json';
    mkdir($blocked);
    $blockedService = new MangaRecommendationService(new MangaRepository($db), hiddenPath: $blocked);
    set_error_handler(static fn (): bool => true);
    try
    {
        $failed = false;
        try
        { $blockedService->hide($id(1)); }
        catch (RuntimeException)
        { $failed = true; }
        $check($failed && is_dir($blocked) && (glob(ROOT . '/storage/.build-*') ?: []) === [], 'Failed publication damaged the target or left a staging file');
    }
    finally
    { restore_error_handler(); }
    $GLOBALS['cacheTestOwner'] = null;
    RecommendationCacheStatement::$collectionReads = 0;
    $check($service()->searchFilters('a') === ['categories' => [], 'authors' => []] && RecommendationCacheStatement::$collectionReads === 0, 'Guest filters must skip collection reads');
}
finally
{
    Config::clear();
    unset($GLOBALS['cacheTestOwner']);
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(ROOT, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
    foreach ($files as $file)
    { if ($file->isDir()) rmdir($file->getPathname()); else unlink($file->getPathname()); }
    rmdir(ROOT);
}
echo "PASS: recommendations and filter cache reuse, single collection read, revisions, query matching/order/limits, custom catalogs, disabled cache and owner isolation.\n";
