<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli')
{ http_response_code(404); exit; }
define('ROOT', dirname(__DIR__, 2));
require ROOT . '/vendor/autoload.php';
require ROOT . '/Framework/Support/Helpers.php';
\Framework\Application\Bootstrap::loadEnvOnly();
\Framework\Config\Environment::set('PROFILER_ENABLED', 'true');
\Framework\Config\Config::clear();

// This CLI supplies an explicit owner without opening an authenticated session.
function user(): ?\App\Models\User\User
{
    return $GLOBALS['searchProfileUser'] ?? null;
}

final class SearchProfileStatement extends PDOStatement
{
    public static array $queries = [];

    protected function __construct()
    {}

    public function execute(?array $params = null): bool
    {
        self::$queries[] = ['sql' => $this->queryString, 'params' => $params];
        return parent::execute($params);
    }
}

$ownerId = filter_var($argv[1] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if ($ownerId === false) throw new InvalidArgumentException('Usage: php scripts/Tools/profile-search.php USER_ID [QUERY ...]');
$owner = new \App\Models\User\User();
$owner->id = $ownerId;
$GLOBALS['searchProfileUser'] = $owner;
$database = new \Framework\Database\Database();
$database->exec('SET SESSION TRANSACTION READ ONLY');
$database->setAttribute(PDO::ATTR_STATEMENT_CLASS, [SearchProfileStatement::class]);
$container = new \Framework\Container\Container();
$container->instance(\Framework\Database\Database::class, $database);
$services = [];
foreach (['Manga', 'Artbook', 'Chinois', 'Figurine', 'Nendoroid', 'Peluche'] as $kind)
    $services[$kind] = $container->get("App\\Services\\{$kind}\\{$kind}ReadService");
$queries = [];
$pages = [1, 5, 20];
$withLists = false;
foreach (array_slice($argv, 2) as $argument)
{
    if ($argument === '--lists')
    { $withLists = true; continue; }
    if (str_starts_with($argument, '--pages='))
    {
        if (!preg_match('/^--pages=([1-9][0-9]{0,3})(,[1-9][0-9]{0,3}){0,9}$/', $argument))
            throw new InvalidArgumentException('Pages: 1..9999, at most 10 pages.');
        $pages = array_values(array_unique(array_map('intval', explode(',', substr($argument, 8)))));
        $withLists = true;
        continue;
    }
    if (str_starts_with($argument, '--')) throw new InvalidArgumentException('Unknown option: ' . $argument);
    $queries[] = $argument;
}
$queries = $queries ?: ['a', 'HSK', 'Berserk', 'Berserk 1', 'absent-search-987654'];
echo "Measurements: 10 samples after 1 warm-up; read-only, current data, sequential execution.\n";
foreach ($queries as $query)
{
    echo 'Query: ' . json_encode($query, JSON_UNESCAPED_UNICODE) . PHP_EOL;
    foreach ($services as $kind => $service)
    {
        profileOperation($kind, static fn (): int => count($service->search($query)->results), $database);
    }
}
if ($withLists)
{
    $perPage = max(1, \Framework\Config\ApplicationConfig::pagination());
    $manga = $container->get(\App\Repositories\Manga\MangaCollectionRepository::class);
    $figurine = $container->get(\App\Repositories\Figurine\FigurineCollectionRepository::class);
    $artbook = $container->get(\App\Repositories\Artbook\ArtbookCollectionRepository::class);
    $nendoroid = $container->get(\App\Repositories\Nendoroid\NendoroidCollectionRepository::class);
    $peluche = $container->get(\App\Repositories\Peluche\PelucheCollectionRepository::class);
    $artbook = $container->get(\App\Repositories\Artbook\ArtbookCollectionRepository::class);
    $nendoroid = $container->get(\App\Repositories\Nendoroid\NendoroidCollectionRepository::class);
    $peluche = $container->get(\App\Repositories\Peluche\PelucheCollectionRepository::class);
    $vocabulary = $container->get(\App\Repositories\Chinois\ChinoisVocabulaireCollectionRepository::class);
    echo "Lists: $perPage items per page (empty pages still execute the pagination SQL).\n";
    foreach ($pages as $page)
    {
        $offset = ($page - 1) * $perPage;
        profileOperation("Manga series page $page OFFSET $offset", static fn (): int => count($manga->findAllFirstTomes('id DESC', $perPage, $page)), $database);
        profileOperation("Figurines page $page OFFSET $offset", static fn (): int => count($figurine->findPaginated($perPage, $page)), $database);
        profileOperation("Artbooks page $page OFFSET $offset", static fn (): int => count($artbook->findPaginated($perPage, $page)), $database);
        profileOperation("Nendoroids page $page OFFSET $offset", static fn (): int => count($nendoroid->findPaginated($perPage, $page)), $database);
        profileOperation("Peluches page $page OFFSET $offset", static fn (): int => count($peluche->findPaginated($perPage, $page)), $database);
        profileOperation("Artbooks page $page OFFSET $offset", static fn (): int => count($artbook->findPaginated($perPage, $page)), $database);
        profileOperation("Nendoroids page $page OFFSET $offset", static fn (): int => count($nendoroid->findPaginated($perPage, $page)), $database);
        profileOperation("Peluches page $page OFFSET $offset", static fn (): int => count($peluche->findPaginated($perPage, $page)), $database);
        profileOperation("Vocabulary mandarin page $page OFFSET $offset", static fn (): int => count($vocabulary->findByLanguePaginated('mandarin', $perPage, $page)), $database);
    }
    echo "Aggregations (without HTTP/dashboard cache):\n";
    $dashboard = $container->get(\App\Services\Home\DashboardStatsService::class);
    $unlocks = $container->get(\App\Repositories\Profile\ProfileUnlockStatsRepository::class);
    profileOperation('Dashboard statistics', static function () use ($dashboard): int
    { $dashboard->dashboard(); return 1; }, $database);
    profileOperation('Profile achievement counters', static function () use ($unlocks): int
    { $unlocks->forAchievements(); return 1; }, $database);
}

/** @param callable(): int $operation */
function profileOperation(string $label, callable $operation, \Framework\Database\Database $database): void
{
    $samples = [];
    $sqlSamples = [];
    $imageSamples = [];
    $plans = [];
    for ($iteration = 0; $iteration < 11; $iteration++)
    {
        SearchProfileStatement::$queries = [];
        \Framework\Debug\Profiler::startRequest();
        $start = hrtime(true);
        $resultCount = $operation();
        $elapsed = (hrtime(true) - $start) / 1_000_000;
        $durations = (new ReflectionProperty(\Framework\Debug\Profiler::class, 'durations'))->getValue();
        $captured = SearchProfileStatement::$queries;
        if ($iteration > 0)
        {
            $samples[] = $elapsed;
            $sqlSamples[] = $durations['database.query'] ?? 0;
            $imageSamples[] = $durations['images.fingerprint'] ?? 0;
        }
        if ($iteration === 10)
            foreach ($captured as $capturedQuery)
            {
                $statement = $database->prepare('EXPLAIN ' . $capturedQuery['sql']);
                $statement->execute($capturedQuery['params']);
                foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $row)
                    $plans[] = array_intersect_key($row, array_flip(['table', 'type', 'key', 'rows', 'Extra']));
            }
    }
    sort($samples);
    sort($sqlSamples);
    sort($imageSamples);
    printf("  %s: median %.3f ms, max %.3f ms, SQL %.3f ms, image hashing %.3f ms, %d queries, %d results\n", $label,
        ($samples[4] + $samples[5]) / 2, max($samples), ($sqlSamples[4] + $sqlSamples[5]) / 2,
        ($imageSamples[4] + $imageSamples[5]) / 2, count($captured), $resultCount);
    echo '    EXPLAIN: ' . json_encode($plans, JSON_UNESCAPED_UNICODE) . PHP_EOL;
}
