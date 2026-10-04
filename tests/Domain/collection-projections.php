<?php

declare(strict_types=1);

use Framework\Container\Container;
use Framework\Database\Database;

require dirname(__DIR__, 2) . '/tests/Support/bootstrap.php';

// Isolated data: no connection to the application's database.
$database = (new ReflectionClass(Database::class))->newInstanceWithoutConstructor();
(new ReflectionMethod(PDO::class, '__construct'))->invoke($database, 'sqlite::memory:');
$database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$database->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
$container = new Container();
$container->instance(Database::class, $database);
$assert = static function (bool $condition, string $message): void
{
    if (! $condition) throw new RuntimeException($message);
};

foreach (['Figurine', 'Nendoroid', 'Peluche', 'Artbook'] as $kind)
{
    $table = strtolower($kind);
    $category = $kind;
    $fields = $kind === 'Artbook' ? 'artbook TEXT, auteur TEXT, serie TEXT' : 'waifu TEXT, origin TEXT, collect INT';
    $database->exec(owned_fixture_sql($database, "CREATE TABLE $table (id INT PRIMARY KEY, slug TEXT, numero INT, thumbnail TEXT, extension TEXT, commentaire TEXT, $fields)"));
    $insert = $database->prepare(owned_fixture_sql($database, "INSERT INTO $table VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"));
    foreach ([[1, 'alpha', 3], [2, 'beta', 1], [3, 'alpha', 1], [4, 'beta', 2]] as [$id, $slug, $number])
    {
        $insert->execute([$id, $slug, $number, $id === 2 ? '' : 'cover', $id === 2 ? '' : 'webp',
            str_repeat('Unused detail text. ', 10000), 'Title ' . $id, 'Origin ' . $id, $kind === 'Artbook' ? null : $id % 2]);
    }

    $repository = $container->get("App\\Repositories\\$category\\{$kind}CollectionRepository");
    $service = $container->get("App\\Services\\$category\\{$kind}ReadService");
    $mapper = new ReflectionMethod($service, match ($kind)
    {
        'Figurine', 'Artbook' => 'mapSeriesItem',
        default => 'mapListItem',
    });
    $model = "App\\Models\\$kind";
    $expectedOrder = [4, 2, 1, 3];
    $rows = [...$repository->findPaginated(2, 1), ...$repository->findPaginated(2, 2)];
    $assert(count($rows) === 4, "$kind: pagination lost items");
    foreach ($rows as $index => $row)
    {
        // Compare the complete view DTO with the old, fully populated model.
        $legacy = $database->query("SELECT * FROM $table WHERE id = " . $expectedOrder[$index])->fetchObject($model);
        $assert($mapper->invoke($service, $row) == $mapper->invoke($service, $legacy), "$kind: list fields or ordering changed");
        $assert($row->commentaire === null, "$kind: list unnecessarily loaded detail text");
    }
    $assert($repository->findPaginated(2, 3) === [], "$kind: out-of-range page is not empty");
    $assert(count($repository->findPaginated(0, 0)) === 1, "$kind: minimum pagination changed");
}

final class RepresentationQueryCounter extends PDOStatement
{
    public static int $executions = 0;
    public function execute(?array $params = null): bool
    {
        self::$executions++;
        return parent::execute($params);
    }
}
$database->setAttribute(PDO::ATTR_STATEMENT_CLASS, [RepresentationQueryCounter::class]);
$artbookStats = $container->get(\App\Repositories\Artbook\ArtbookStatsRepository::class);
$database->exec('DELETE FROM artbook');
RepresentationQueryCounter::$executions = 0;
$assert($artbookStats->findMostRepresented() === null, 'Empty representation changed');
$assert(RepresentationQueryCounter::$executions === 1, 'Representation needs one query');
$database->exec(owned_fixture_sql($database, "INSERT INTO artbook (id, auteur, serie, thumbnail, extension) VALUES
    (1, 'Author', NULL, 'author', 'webp'), (2, NULL, 'Series', 'series', 'webp')"));
$winner = $artbookStats->findMostRepresented();
$assert($winner->name === 'Author' && $winner->total === 1, 'Author must win equal counts');
$database->exec(owned_fixture_sql($database, "INSERT INTO artbook (id, auteur, serie, thumbnail, extension) VALUES (3, NULL, 'Series', 'series', 'webp')"));
$winner = $artbookStats->findMostRepresented();
$assert($winner->name === 'Series' && $winner->total === 2, 'Series winner changed');
$assert($winner->thumbnailUrl === 'images/artbook/thumbnail/series.webp', 'Representation image changed');
$database->exec("DELETE FROM artbook WHERE serie IS NOT NULL");
$assert($artbookStats->findMostRepresented()->name === 'Author', 'Author-only representation changed');

$database->exec(owned_fixture_sql($database, 'CREATE TABLE manga (id INT PRIMARY KEY, slug TEXT, numero INT, livre TEXT, thumbnail TEXT, extension TEXT, commentaire TEXT)'));
$database->exec(owned_fixture_sql($database, "INSERT INTO manga VALUES (1, 'alpha', 3, 'Alpha', 'cover', 'webp', 'detail'),
    (2, 'beta', 1, 'Beta', '', '', 'detail'), (3, 'alpha', 2, 'Alpha', 'cover', 'webp', 'detail')"));
$database->exec("ALTER TABLE manga ADD COLUMN statut TEXT DEFAULT 'en_cours'");
$database->exec('ALTER TABLE manga ADD COLUMN note INT DEFAULT 6');
$database->exec('ALTER TABLE manga ADD COLUMN lu INT DEFAULT 0');
$database->exec('PRAGMA query_only = ON');
$repository = $container->get(\App\Repositories\Manga\MangaRepository::class);
$service = $container->get(\App\Services\Manga\MangaReadService::class);
$mapper = new ReflectionMethod($service, 'mapSeriesItem');
$cards = $repository->findBySlug('alpha');
$assert(count($cards) === 2 && $cards[0]->numero === 3, 'Series order changed');
foreach ($cards as $card)
{
    $legacy = $repository->findOneBySlugAndNumero($card->slug, $card->numero);
    $assert($mapper->invoke($service, $card) == $mapper->invoke($service, $legacy), 'Series card data changed');
    $assert($card->commentaire === null && $legacy->commentaire === 'detail', 'Series still loads detail text');
}
$assert($repository->findBySlug('missing') === [], 'Missing series changed');
$stats = $container->get(\App\Repositories\Manga\MangaStatsRepository::class);
$last = $stats->findLastAddedDto();
$assert($last !== null && $last->id === 3 && $last->numero === 2 && $last->url === 'manga/series/alpha/2', 'Latest manga changed');
$top = $stats->topLongestSeriesDto();
$assert(count($top) === 2 && $top[0]->id === 3 && $top[0]->total === 2 && $top[0]->url === 'manga/series/alpha', 'Series representative or count changed');
$assert($top[1]->thumbnailUrl === 'images/manga/placeholder-manga.webp', 'Missing cover fallback changed');

echo "PASS: four collection DTOs, ordering, pagination, omitted detail text and dashboard manga cards.\n";
