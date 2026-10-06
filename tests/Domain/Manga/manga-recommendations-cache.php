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
$db->exec('CREATE TABLE manga (user_id INT, slug TEXT, livre TEXT, numero INT)');
$db->exec("INSERT INTO manga VALUES (1, 'owned', 'Owned', 1), (2, 'suggestion', 'Suggestion', 1)");
$id = static fn (int $n): string => sprintf('00000000-0000-0000-0000-%012d', $n);
$catalog = ['series' => [['id' => $id(0), 'title' => 'Owned'], ['id' => $id(1), 'title' => 'Suggestion'], ['id' => $id(2), 'title' => 'Second owned']],
    'kinds' => [['title' => 'Action', 'series_ids' => [$id(0), $id(1), $id(2)]]],
    'details' => [$id(1) => ['firstRelease' => '2026-10-07', 'volumeCount' => 2, 'edition' => 'Standard']]];
$write = static function () use (&$catalog): void
{ file_put_contents(ROOT . '/storage/manga-recommendations.json', json_encode($catalog, JSON_THROW_ON_ERROR)); };
$service = static fn (): MangaRecommendationService => new MangaRecommendationService(new MangaRepository($db));
$check = static function (bool $condition, string $message): void
{ if (!$condition) throw new RuntimeException($message); };
try
{
    $write();
    $check(count($service()->all()) === 2, 'Initial recommendations missing');
    $check($service()->setFavorite($id(1), true)->success, 'Cannot save fixture favorite');
    $check($service()->favorites()[0]['firstRelease'] === '07/10/2026', 'Initial favorite date missing');
    $files = glob(ROOT . '/storage/cache/*.cache');
    $check(count($files) === 2, 'Persistent recommendations and favorites cache not created');
    $snapshots = array_map('file_get_contents', $files);
    $service()->all();
    $service()->favorites();
    $check(array_map('file_get_contents', $files) === $snapshots, 'Warm cache was rewritten unnecessarily');

    $catalog['series'][1]['title'] = 'Updated suggestion';
    $catalog['details'][$id(1)]['firstRelease'] = null;
    $write();
    $fresh = $service()->favorites()[0];
    $check($fresh['title'] === 'Updated suggestion' && $fresh['firstRelease'] === null, 'Catalog revision did not replace cached favorite title and removed date');
    $check(in_array('Updated suggestion', array_column($service()->all(), 'title'), true), 'Catalog revision did not replace recommendations');

    $db->exec("INSERT INTO manga VALUES (1, 'second-owned', 'Second owned', 1)");
    $check($service()->all()[0]['score'] === 2 && $service()->favorites()[0]['score'] === 2, 'Collection revision did not replace cached scores');
    $check($service()->hide($id(1))->success && $service()->all() === [], 'Hidden revision did not replace cached recommendations');
    $check($service()->favorites()[0]['score'] === 2, 'Hiding changed favorite score');
    $check(count(glob(ROOT . '/storage/cache/*.cache')) === 2, 'Revisions accumulated cache entries');

    $owner->id = 2;
    $check($service()->favorites() === [] && !in_array($id(1), array_column($service()->all(), 'id'), true), 'Owner cache isolation failed');
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
echo "PASS: persistent cache reuse, catalog/collection/hidden revisions, removed favorite dates, bounded entries and owner isolation.\n";
