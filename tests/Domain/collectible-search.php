<?php

declare(strict_types=1);

require dirname(__DIR__, 2) . '/tests/Support/bootstrap.php';

use Framework\Database\Database;

$db = (new ReflectionClass(Database::class))->newInstanceWithoutConstructor();
(new ReflectionMethod(PDO::class, '__construct'))->invoke($db, 'sqlite::memory:');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
$check = static function (bool $condition, string $message): void
{
    if (!$condition) throw new RuntimeException($message);
};
foreach (['Figurine', 'Nendoroid', 'Peluche'] as $kind)
{
    $table = strtolower($kind);
    $db->exec(owned_fixture_sql($db, "CREATE TABLE $table (id INT PRIMARY KEY, slug TEXT, numero INT, origin TEXT, waifu TEXT, thumbnail TEXT, extension TEXT)"));
    $insert = $db->prepare(owned_fixture_sql($db, "INSERT INTO $table VALUES (?, ?, ?, ?, ?, 'cover', 'webp')"));
    for ($i = 25; $i >= 1; $i--) $insert->execute([$i, 'etoile-bleue', $i, 'Origin', 'Étoile Bleue']);
    $class = "App\\Repositories\\$kind\\{$kind}SearchRepository";
    $model = "App\\Models\\$kind\\$kind";
    $repository = new $class($db);
    foreach (["  Étoile   Bleue  ", 'etoile-bleue', 'Origin'] as $query)
    {
        $rows = $repository->search($query);
        $check(count($rows) === 20, "$kind: limit or matching changed");
        foreach ($rows as $index => $row)
        {
            $check($row instanceof $model && $row->numero === $index + 1, "$kind: hydration or ordering changed");
            $check($row->thumbnail === 'cover' && $row->extension === 'webp', "$kind: projection changed");
        }
    }
    $limited = $repository->search('Origin', 5);
    $check(array_map(static fn ($item): int => $item->numero, $limited) === [1, 2, 3, 4, 5], "$kind: limited search changed order");
    $check(count($repository->search('Origin', 0)) === 1 && count($repository->search('Origin', PHP_INT_MAX)) === 20,
        "$kind: invalid limits are not bounded");
    foreach (['   ', 'absent', '!!!'] as $query) $check($repository->search($query) === [], "$kind: empty/no-match search changed");
}
echo "PASS: three collectible searches preserve normalization, matching, model types, ordering and limits.\n";

$db->exec(owned_fixture_sql($db, 'CREATE TABLE chinois_grammaire (id INT PRIMARY KEY, titre TEXT, structure TEXT, explication TEXT, niveau TEXT)'));
$db->exec(owned_fixture_sql($db, 'CREATE TABLE chinois_vocabulaire (id INT PRIMARY KEY, mot TEXT, pinyin TEXT, traduction TEXT, langue TEXT)'));
for ($i = 1; $i <= 8; $i++)
    $db->exec(owned_fixture_sql($db, "INSERT INTO chinois_vocabulaire VALUES ($i, 'needle', 'needle', 'Translation', 'mandarin')"));
for ($i = 1; $i <= 2; $i++)
    $db->exec(owned_fixture_sql($db, "INSERT INTO chinois_grammaire VALUES ($i, 'needle', 'needle', 'Explanation', 'HSK1')"));
$learning = new \App\Repositories\Chinois\ChinoisSearchRepository($db);
$signature = static fn (array $items): array => array_map(static fn ($item): string => $item->type . ':' . $item->id, $items);
$check($signature($learning->search('needle', 5)) === array_slice($signature($learning->search('needle')), 0, 5),
    'Limited learning search must fill remaining slots with vocabulary');
for ($i = 3; $i <= 8; $i++)
    $db->exec(owned_fixture_sql($db, "INSERT INTO chinois_grammaire VALUES ($i, 'needle', 'needle', 'Explanation', 'HSK1')"));
$check($signature($learning->search('needle', 5)) === array_slice($signature($learning->search('needle')), 0, 5),
    'Limited learning search must preserve grammar priority');
echo "PASS: bounded search limits and grammar/vocabulary priority.\n";
