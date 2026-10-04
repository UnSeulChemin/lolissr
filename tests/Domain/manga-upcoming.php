<?php

declare(strict_types=1);

require dirname(__DIR__, 2) . '/tests/Support/bootstrap.php';

use App\Repositories\Manga\MangaRepository;
use App\Services\Manga\UpcomingMangaService;

use Framework\Database\Database;

$db = (new ReflectionClass(Database::class))->newInstanceWithoutConstructor();
(new ReflectionMethod(PDO::class, '__construct'))->invoke($db, 'sqlite::memory:');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->exec('CREATE TABLE manga (user_id INT, slug TEXT, numero INT)');
$db->exec("INSERT INTO manga VALUES (1, 'rave', 16), (2, 'rave', 18)");
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
    $assert(array_column($service->forSeries('rave'), 'number') === [17, 18], 'Owned, expired, invalid or foreign releases leaked; sorting failed');
    $assert($service->forSeries('unknown') === [], 'Unknown series leaked');
    $GLOBALS['testCurrentUser'] = null;
    $assert($service->forSeries('rave') === [], 'Anonymous access leaked');
    unset($GLOBALS['testCurrentUser']);
    file_put_contents($path, '{');
    $assert($service->forSeries('rave') === [], 'Corrupt snapshot broke the page');
    unlink($path);
    $assert($service->forSeries('rave') === [], 'Missing snapshot broke the page');
}
finally
{
    unset($GLOBALS['testCurrentUser']);
    if (is_file($path)) unlink($path);
}
echo "PASS: forthcoming releases isolate owners, filter owned/expired/invalid volumes, sort and tolerate missing/corrupt snapshots.\n";
