<?php

declare(strict_types=1);

require dirname(__DIR__, 2) . '/tests/Support/bootstrap.php';

use Framework\Container\Container;
use Framework\Database\Database;
use Framework\Http\Exceptions\NotFoundException;

$mysql = ($argv[1] ?? '') === 'mysql';
if ($mysql)
{
    \Framework\Application\Bootstrap::loadEnvOnly();
    $db = new Database();
}
else
{
    $db = (new ReflectionClass(Database::class))->newInstanceWithoutConstructor();
    (new ReflectionMethod(PDO::class, '__construct'))->invoke($db, 'sqlite::memory:');
}
$temporary = $mysql ? 'TEMPORARY ' : '';
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
$container = new Container();
$container->instance(Database::class, $db);
$check = static function (bool $ok, string $message): void
{
    if (!$ok) throw new RuntimeException($message);
};
// MySQL temporary tables cannot be joined to themselves; exercise the manga
// query on SQLite and the exact section collation on both engines.
if (!$mysql)
{
$db->exec(owned_fixture_sql($db, "CREATE {$temporary}TABLE manga (id INT PRIMARY KEY, slug TEXT, numero INT, livre TEXT,
    thumbnail VARCHAR(255) DEFAULT '', extension VARCHAR(10) DEFAULT '', statut VARCHAR(20) DEFAULT 'termine', note INT DEFAULT 8, lu INT DEFAULT 1)"));
for ($id = 1; $id <= 123; $id++) $db->exec(owned_fixture_sql($db, "INSERT INTO manga (id, slug, numero, livre) VALUES ($id, 'longue', $id, 'Longue')"));
$manga = $container->get(App\Services\Manga\MangaReadService::class);
$first = $manga->showSeries('longue');
$check($first !== null && count($first->mangas) === $first->perPage && $first->totalSeries === 123, 'Unbounded series');
$numbers = [];
for ($page = 1; $page <= $first->totalPages; $page++)
{
    $data = $manga->showSeries('longue', $page);
    $check($data !== null && $data->currentPage === $page, 'Lost current page');
    array_push($numbers, ...array_column($data->mangas, 'numero'));
}
$check($numbers === range(123, 1), 'Missing, duplicate or misordered volumes');
$check($manga->showSeries('longue', PHP_INT_MAX) === null && $manga->showSeries('missing') === null, 'Missing/out-of-range series');
$stats = $container->get(App\Repositories\Manga\MangaStatsRepository::class);
for ($id = 124; $id <= 183; $id++) $db->exec(owned_fixture_sql($db, "INSERT INTO manga (id, slug, numero, livre) VALUES ($id, 'series-$id', 1, 'Series')"));
$check($stats->countCompletedSeries() === 61 && $stats->countCompletedSeries(25) === 25, 'Capped achievement count');
}

$db->exec(owned_fixture_sql($db, "CREATE {$temporary}TABLE chinois_grammaire (id INT PRIMARY KEY, niveau TEXT, section TEXT, categorie TEXT,
    titre TEXT, structure TEXT, abreviation TEXT, phrase TEXT, pinyin TEXT, traduction TEXT, explication TEXT,
    position INT, section_position INT, categorie_position INT, maitrise INT DEFAULT 0, xp_rewarded INT DEFAULT 0)"));
$insert = $db->prepare(owned_fixture_sql($db, 'INSERT INTO chinois_grammaire (id, niveau, section, categorie, titre, explication,
    position, section_position, categorie_position) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'));
foreach (['École', 'Ecole', 'ecole-2', '!!!', 'section-2', '中文'] as $i => $title)
{
    $insert->execute([$i + 1, 'HSK1', $title, 'Categorie', 'Rule ' . $i, str_repeat('Explanation ', 1000), 1, $i, 1]);
}
$grammar = $container->get(App\Services\Chinois\ChinoisReadService::class);
$first = $grammar->hsk('HSK1');
$check(count($first->menu) === 6 && count($first->sections) === 1, 'Grammar must load one section');
$check(array_column($first->menu, 'id') === ['ecole', 'ecole-3', 'ecole-2', 'section-3', 'section-2', 'zhong-wen'], 'Menu anchors changed');
foreach ($first->menu as $i => $section)
{
    $data = $grammar->hsk('HSK1', $section->id);
    $check(count($data->sections) === 1 && $data->sections[0]->id === $section->id
        && $data->sections[0]->categories[0]->grammaires[0]->id === $i + 1, 'Wrong section loaded');
    $check($section->categories === [], 'Menu loaded rule content');
}
$check($grammar->hsk('HSK2')->sections === [], 'Empty level');
try
{ $grammar->hsk('HSK1', 'missing'); throw new RuntimeException('Unknown section accepted'); }
catch (NotFoundException)
{}
echo $mysql ? "PASS: MySQL section menu/selection and exact names under database collation (temporary table).\n"
    : "PASS: bounded series, section menu/selection, stable anchors, missing pages and capped achievements.\n";
