<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli')
{ http_response_code(404); exit; }
define('ROOT', dirname(__DIR__, 2));
require ROOT . '/vendor/autoload.php';
require ROOT . '/Framework/Support/Helpers.php';
\Framework\Application\Bootstrap::loadEnvOnly();
\Framework\Config\Env::set('PROFILER_ENABLED', 'true');
\Framework\Config\Config::clear();

// This CLI supplies an explicit owner without opening an authenticated session.
function user(): ?\App\Models\User
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
$owner = new \App\Models\User();
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
$queries = array_slice($argv, 2) ?: ['a', 'HSK', 'Berserk', 'Berserk 1', 'absent-search-987654'];
foreach ($queries as $query)
{
    echo 'Query: ' . json_encode($query, JSON_UNESCAPED_UNICODE) . PHP_EOL;
    foreach ($services as $kind => $service)
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
            $result = $service->search($query);
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
        printf("  %s: median %.3f ms, SQL %.3f ms, image hashing %.3f ms, %d queries, %d results\n", $kind, $samples[5], $sqlSamples[5], $imageSamples[5], count($captured), count($result->results));
        echo '    EXPLAIN: ' . json_encode($plans, JSON_UNESCAPED_UNICODE) . PHP_EOL;
    }
}
