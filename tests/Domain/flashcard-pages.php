<?php

declare(strict_types=1);

require dirname(__DIR__, 2) . '/tests/Support/bootstrap.php';

use App\Services\Chinois\ChinoisReadService;
use Framework\Container\Container;
use Framework\Database\Database;

$db = (new ReflectionClass(Database::class))->newInstanceWithoutConstructor();
(new ReflectionMethod(PDO::class, '__construct'))->invoke($db, 'sqlite::memory:');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
final class FlashcardQueryCounter extends PDOStatement
{
    public static int $executions = 0;
    public function execute(?array $params = null): bool
    {
        self::$executions++;
        return parent::execute($params);
    }
}
$db->setAttribute(PDO::ATTR_STATEMENT_CLASS, [FlashcardQueryCounter::class]);
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
        FlashcardQueryCounter::$executions = 0;
        $page = $service->flashcardPage($grammar, $offset);
        $check(FlashcardQueryCounter::$executions === 1, 'Count and cards must share one statement snapshot');
        $check($page['total'] === 120 && $page['offset'] === $offset, 'Count or offset changed');
        $check(count($page['cards']) === min(50, 120 - $offset), 'Batch exceeds 50 or loses cards');
        foreach ($page['cards'] as $card) $ids[] = $card->id;
    }
    $check($ids === range(2, 240, 2), 'Sparse IDs: lost, duplicated, mastered or unordered cards');
    foreach ([[0, false, 0, range(2, 100, 2)], [100, false, 50, range(102, 200, 2)],
        [102, true, 0, range(2, 100, 2)], [2, true, 70, range(142, 240, 2)],
        [240, false, 0, range(2, 100, 2)], [PHP_INT_MAX, false, 0, range(2, 100, 2)]] as [$cursor, $previous, $offset, $expected])
    {
        FlashcardQueryCounter::$executions = 0;
        $page = $service->flashcardCursor($grammar, $cursor, $previous);
        $check(FlashcardQueryCounter::$executions === 1, 'Cursor count/cards require one snapshot');
        $check($page['total'] === 120 && $page['offset'] === $offset
            && array_column($page['cards'], 'id') === $expected, 'Cursor direction, wrap or count failed');
    }
    $check($service->flashcardPage($grammar, PHP_INT_MAX)['offset'] === 100, 'Oversized offset');
    $check($service->flashcardPage($grammar, -1)['offset'] === 0, 'Negative offset');
    $db->exec("DELETE FROM $table WHERE id <= 102");
    $cursorPage = $service->flashcardCursor($grammar, 102);
    $check($cursorPage['offset'] === 0 && $cursorPage['cards'][0]->id === 104 && $cursorPage['total'] === 69,
        'Deleted cursor must seek to the next surviving card');
    $page = $service->flashcardPage($grammar, 100);
    $check($page['total'] === 69 && $page['offset'] === 50 && count($page['cards']) === 19, 'Concurrent shrink not reflected');
    $db->exec("UPDATE $table SET maitrise = 1");
    $check($service->flashcardPage($grammar, 50)['cards'] === [], 'Exhausted deck');
    $check($service->flashcardCursor($grammar) === ['cards' => [], 'total' => 0, 'offset' => 0], 'Empty cursor deck');
}
echo "PASS: bounded flashcard pages, sparse IDs, mastery filtering, counts and shrinking decks.\n";
