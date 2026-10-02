<?php

declare(strict_types=1);

require dirname(__DIR__, 2) . '/phpstan-bootstrap.php';

use App\Services\Chinois\ChinoisReadService;
use Framework\Container\Container;
use Framework\Database\Database;

$db = (new ReflectionClass(Database::class))->newInstanceWithoutConstructor();
(new ReflectionMethod(PDO::class, '__construct'))->invoke($db, 'sqlite::memory:');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
$container = new Container();
$container->instance(Database::class, $db);
$service = $container->get(ChinoisReadService::class);
$check = static function (bool $condition, string $message): void {
    if (!$condition) throw new RuntimeException($message);
};

foreach ([false, true] as $grammar)
{
    $table = $grammar ? 'chinois_grammaire' : 'chinois_vocabulaire';
    $fields = $grammar
        ? 'niveau TEXT, section TEXT, categorie TEXT, titre TEXT, structure TEXT, abreviation TEXT, phrase TEXT, explication TEXT, position INT'
        : 'langue TEXT, mot TEXT, type TEXT, exemple TEXT';
    $db->exec("CREATE TABLE $table (id INT PRIMARY KEY, maitrise INT DEFAULT 0, xp_rewarded INT DEFAULT 0, pinyin TEXT, traduction TEXT, $fields)");
    $check($service->flashcardPage($grammar) === ['cards' => [], 'total' => 0, 'offset' => 0], 'Empty deck');
    for ($i = 1; $i <= 123; $i++)
    {
        $db->exec("INSERT INTO $table (id, maitrise) VALUES (" . ($i * 2) . ', ' . ($i > 120 ? 1 : 0) . ')');
    }
    $ids = [];
    foreach ([0, 50, 100] as $offset)
    {
        $page = $service->flashcardPage($grammar, $offset);
        $check($page['total'] === 120 && $page['offset'] === $offset, 'Count or offset changed');
        $check(count($page['cards']) === min(50, 120 - $offset), 'Batch exceeds 50 or loses cards');
        foreach ($page['cards'] as $card) $ids[] = $card->id;
    }
    $check($ids === range(2, 240, 2), 'Sparse IDs: lost, duplicated, mastered or unordered cards');
    $check($service->flashcardPage($grammar, PHP_INT_MAX)['offset'] === 100, 'Oversized offset');
    $check($service->flashcardPage($grammar, -1)['offset'] === 0, 'Negative offset');
    $db->exec("DELETE FROM $table WHERE id <= 102");
    $page = $service->flashcardPage($grammar, 100);
    $check($page['total'] === 69 && $page['offset'] === 50 && count($page['cards']) === 19, 'Concurrent shrink not reflected');
    $db->exec("UPDATE $table SET maitrise = 1");
    $check($service->flashcardPage($grammar, 50)['cards'] === [], 'Exhausted deck');
}
echo "PASS: bounded flashcard pages, sparse IDs, mastery filtering, counts and shrinking decks.\n";
