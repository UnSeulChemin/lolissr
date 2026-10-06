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
    foreach (['   ', 'absent', '!!!'] as $query) $check($repository->search($query) === [], "$kind: empty/no-match search changed");
}
echo "PASS: three collectible searches preserve normalization, matching, model types, ordering and limits.\n";
