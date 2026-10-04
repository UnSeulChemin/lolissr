<?php
declare(strict_types=1);
require dirname(__DIR__, 2) . '/tests/Support/bootstrap.php';

use Framework\Config\Config;
use Framework\Database\Database;

Config::prime(['app' => ['env' => 'local']]);
$database = (new ReflectionClass(Database::class))->newInstanceWithoutConstructor();
(new ReflectionMethod(PDO::class, '__construct'))->invoke($database, 'sqlite::memory:');
$database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$database->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
foreach (['Manga', 'Artbook', 'Figurine', 'Nendoroid', 'Peluche'] as $kind)
{
    $table = strtolower($kind);
    $domain = $kind;
    $class = "App\\Repositories\\$domain\\{$kind}Repository";
    $repository = new $class($database);
    $database->exec(owned_fixture_sql($database, "CREATE TABLE $table (id INTEGER PRIMARY KEY, slug TEXT, numero INT, thumbnail TEXT, extension TEXT)"));
    $database->exec(owned_fixture_sql($database, "INSERT INTO $table VALUES (1, 'fixture', 1, 'old', 'png')"));
    $read = $kind === 'Manga' ? 'findRecordBySlugAndNumero' : 'findOneBySlugAndNumero';
    $observed = $repository->$read('fixture', 1);
    // Interleave a competing deletion/recreation after the service's initial read.
    $database->exec("DELETE FROM $table WHERE id = 1");
    $database->exec(owned_fixture_sql($database, "INSERT INTO $table VALUES (2, 'fixture', 1, 'replacement', 'png')"));
    if ($database->transaction(fn () => $repository->deleteById($observed->id)))
        throw new RuntimeException('Missing identity reported as deleted: ' . $kind);
    if ((int)$database->query("SELECT COUNT(*) FROM $table WHERE id = 2")->fetchColumn() !== 1)
        throw new RuntimeException('Replacement deleted: ' . $kind);
    if (!$database->transaction(fn () => $repository->deleteById(2)))
        throw new RuntimeException('Existing identity not deleted: ' . $kind);
    if ($repository->deleteById(2)) throw new RuntimeException('Repeated deletion succeeded: ' . $kind);
}
Config::clear();
echo "PASS: five collections preserve replacements and report actual deletion by identity.\n";
