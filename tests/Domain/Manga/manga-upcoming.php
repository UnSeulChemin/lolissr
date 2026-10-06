<?php

declare(strict_types=1);

require dirname(__DIR__, 3) . '/tests/Support/bootstrap.php';

use App\Repositories\Manga\MangaRepository;
use App\Services\Manga\UpcomingMangaService;

use Framework\Database\Database;

$db = (new ReflectionClass(Database::class))->newInstanceWithoutConstructor();
(new ReflectionMethod(PDO::class, '__construct'))->invoke($db, 'sqlite::memory:');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->exec('CREATE TABLE manga (user_id INT, slug TEXT, numero INT)');
$db->exec("INSERT INTO manga (user_id, slug, numero) VALUES (1, 'rave', 16), (2, 'rave', 18)");
$db->exec("ALTER TABLE manga ADD COLUMN livre TEXT DEFAULT 'Rave'");
$path = tempnam(sys_get_temp_dir(), 'manga-upcoming-');
$service = new UpcomingMangaService(new MangaRepository($db), $path);
$assert = static function (bool $ok, string $message): void
{ if (!$ok) throw new RuntimeException($message); };
$entry = static fn (int $number, string $date): array => ['number' => $number, 'release_date' => $date, 'id' => '01234567-89ab-cdef-0123-456789abcdef'];
try
{
    $future = date('Y-m-d', strtotime('+10 days'));
    file_put_contents($path, json_encode(['users' => ['1' => ['rave' => ['upcoming' => [
        $entry(18, $future), $entry(16, $future), $entry(17, $future),
        $entry(19, date('Y-m-d')), $entry(20, '2099-02-30'), $entry(0, $future)
    ]]], '2' => ['rave' => ['upcoming' => [$entry(99, $future)]]]]], JSON_THROW_ON_ERROR));
    $assert(array_column($service->forSeries('rave'), 'number') === [19, 18, 17], 'Owned, invalid or foreign releases leaked; sorting failed');
    $all = $service->all();
    $summary = $service->summary();
    $assert($summary['upcomingCount'] === 2 && $summary['missingCount'] === 1 && $summary['nextRelease'] == $all[1], 'Dashboard summary differs from sorted releases');
    $assert(count($all) === 3 && $all[0]->number === 19 && $all[0]->slug === 'rave' && $all[0]->title === 'Rave', 'Global release list lost title, ordering or owner isolation');
    $assert($service->forSeries('rave')[0]->isUpcoming === false && $service->forSeries('rave')[1]->isUpcoming === true, 'Released/future status incorrect');
    $db->exec("DELETE FROM manga WHERE user_id = 1");
    $db->exec("INSERT INTO manga (user_id, slug, numero) VALUES (1, 'rave', 14), (1, 'rave', 12), (1, 'rave', 10), (1, 'rave', 8)");
    file_put_contents($path, json_encode(['users' => ['1' => ['rave' => ['upcoming' => array_map(static fn ($n) => $entry($n, $future), [19, 13, 11, 9, 7])]]]], JSON_THROW_ON_ERROR));
    $assert(array_column($service->forSeries('rave', 1, 2), 'number') === [19, 13], 'First-page missing volumes incorrect');
    $assert(array_column($service->forSeries('rave', 2, 2), 'number') === [11, 9, 7], 'Later missing volumes lost or duplicated');
    $db->exec("INSERT INTO manga (user_id, slug, numero) VALUES (1, 'rave', 13)");
    $assert(!in_array(13, array_column($service->forSeries('rave'), 'number'), true), 'Purchased volume remained gray before next sync');
    $assert($service->forSeries('unknown') === [], 'Unknown series leaked');
    $db->exec("INSERT INTO manga (user_id, slug, numero, livre) VALUES (1, 'alpha', 1, 'Alpha')");
    file_put_contents($path, json_encode(['users' => ['1' => [
        'rave' => ['upcoming' => [$entry(18, $future), $entry(17, $future)]],
        'alpha' => ['upcoming' => [$entry(3, $future), $entry(2, $future)]]
    ]]], JSON_THROW_ON_ERROR));
    $all = $service->all();
    $summary = $service->summary();
    $assert($summary['upcomingCount'] === 4 && $summary['missingCount'] === 0 && $summary['nextRelease'] == $all[0] && $summary['nextRelease']->number === 2 && $summary['nextRelease']->title === 'Alpha', 'Summary lost title/number tie-breaking or refreshed snapshot');
    $GLOBALS['testCurrentUser'] = null;
    $assert($service->forSeries('rave') === [], 'Anonymous access leaked');
    unset($GLOBALS['testCurrentUser']);
    file_put_contents($path, '{');
    $assert($service->forSeries('rave') === [], 'Corrupt snapshot broke the page');
    $assert($service->summary() === ['upcomingCount' => 0, 'missingCount' => 0, 'nextRelease' => null], 'Corrupt snapshot broke dashboard summary');
    unlink($path);
    $assert($service->forSeries('rave') === [], 'Missing snapshot broke the page');
}
finally
{
    unset($GLOBALS['testCurrentUser']);
    if (is_file($path)) unlink($path);
}
echo "PASS: unowned releases isolate owners, distinguish released/future dates, filter owned/invalid volumes, paginate and sort and tolerate missing/corrupt snapshots.\n";
