<?php

declare(strict_types=1);

require dirname(__DIR__, 3) . '/tests/Support/bootstrap.php';

use App\Support\Search\SearchQuery;

use Framework\Database\Database;
use Framework\Http\Exceptions\ValidationException;

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
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
$temporary = $mysql ? 'TEMPORARY ' : '';
$schemas = [
    'manga' => ['livre', 'thumbnail', 'extension', 'note', 'lu'],
    'artbook' => ['artbook', 'auteur', 'serie', 'thumbnail', 'extension', 'company'],
    'figurine' => ['waifu', 'origin', 'thumbnail', 'extension'],
    'nendoroid' => ['waifu', 'origin', 'thumbnail', 'extension'],
    'peluche' => ['waifu', 'origin', 'thumbnail', 'extension'],
    'chinois_grammaire' => ['titre', 'structure', 'explication', 'niveau'],
    'chinois_vocabulaire' => ['mot', 'pinyin', 'traduction', 'langue']
];
$symbols = ['%', '_', '!', '\\'];
foreach ($schemas as $table => $fields)
{
    $columns = implode(', ', array_map(static fn (string $field): string => "$field TEXT", $fields));
    $db->exec("CREATE {$temporary}TABLE $table (id INT PRIMARY KEY, user_id INT, slug VARCHAR(255), numero INT, $columns)");
    $insert = $db->prepare("INSERT INTO $table VALUES (" . implode(', ', array_fill(0, count($fields) + 4, '?')) . ')');
    foreach ([...$symbols, 'ordinary'] as $index => $symbol)
    {
        $values = array_map(static fn (string $field): mixed => match ($field)
        {
            'thumbnail' => 'cover', 'extension' => 'webp', 'note' => 0, 'lu' => 0,
            'niveau' => 'HSK1', 'langue' => 'mandarin',
            default => 'literal ' . $symbol . ' value'
        }, $fields);
        $insert->execute([$index + 1, 1, 'fixture-' . ($index + 1), 1, ...$values]);
    }
}
foreach (['Manga', 'Artbook', 'Figurine', 'Nendoroid', 'Peluche', 'Chinois'] as $kind)
{
    $class = "App\\Repositories\\$kind\\{$kind}SearchRepository";
    $repository = new $class($db);
    foreach ($symbols as $index => $symbol)
    {
        $rows = $repository->search($symbol);
        if (count($rows) !== ($kind === 'Chinois' ? 2 : 1)) throw new RuntimeException("$kind: literal symbol broadened results: $symbol");
        foreach ($rows as $row)
        {
            $correct = $kind === 'Chinois' ? $row->id === $index + 1 : $row->slug === 'fixture-' . ($index + 1);
            if (!$correct) throw new RuntimeException("$kind: wrong literal symbol match: $symbol");
        }
    }
    foreach ([str_repeat('a', 201), str_repeat('界', 201)] as $invalid)
    {
        $rejected = false;
        try
        { $repository->search($invalid); }
        catch (ValidationException $exception)
        { $rejected = $exception->getStatusCode() === 422; }
        if (!$rejected) throw new RuntimeException("$kind: oversized search accepted.");
    }
    foreach ([str_repeat('a', 200), str_repeat('界', 200)] as $valid) $repository->search($valid);
}
if (SearchQuery::validate('  titre  ') !== 'titre') throw new RuntimeException('Search trimming changed.');
echo 'PASS: literal SQL symbols and backslashes, 200/201 ASCII and Unicode boundaries across all search repositories (' . ($mysql ? 'MySQL temporary tables' : 'SQLite') . ").\n";
