<?php

declare(strict_types=1);

require dirname(__DIR__, 2) . '/tests/Support/bootstrap.php';

use Framework\Container\Container;
use Framework\Database\Database;

final class ConcurrentFlashcardStatement extends PDOStatement
{
    public static ?Closure $afterRead = null;
    public function execute(?array $params = null): bool
    {
        $result = parent::execute($params);
        $callback = self::$afterRead;
        self::$afterRead = null;
        if ($callback !== null) $callback();
        return $result;
    }
}
$path = tempnam(sys_get_temp_dir(), 'flashcard-snapshot-');
if ($path === false) throw new RuntimeException('Cannot create isolated database');
try
{
    (static function (string $path): void
    {
        $reader = (new ReflectionClass(Database::class))->newInstanceWithoutConstructor();
        (new ReflectionMethod(PDO::class, '__construct'))->invoke($reader, 'sqlite:' . $path);
        $reader->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $reader->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
        $reader->exec('PRAGMA journal_mode=WAL');
        $writer = new PDO('sqlite:' . $path, options: [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $container = new Container();
        $container->instance(Database::class, $reader);
        $service = $container->get(\App\Services\Chinois\ChinoisReadService::class);
        foreach ([false, true] as $grammar)
        {
            $table = $grammar ? 'chinois_grammaire' : 'chinois_vocabulaire';
            $fields = $grammar
                ? 'niveau TEXT, section TEXT, categorie TEXT, titre TEXT, structure TEXT, abreviation TEXT, phrase TEXT, explication TEXT, position INT'
                : 'langue TEXT, mot TEXT, type TEXT, exemple TEXT';
            $reader->exec("CREATE TABLE $table (id INT PRIMARY KEY, maitrise INT DEFAULT 0, xp_rewarded INT DEFAULT 0, pinyin TEXT, traduction TEXT, $fields)");
            for ($id = 1; $id <= 100; $id++) $reader->exec("INSERT INTO $table (id) VALUES ($id)");
            $reader->setAttribute(PDO::ATTR_STATEMENT_CLASS, [ConcurrentFlashcardStatement::class]);
            // Commit from a second connection after the read starts, before its rows are consumed.
            ConcurrentFlashcardStatement::$afterRead = static fn () => $writer->exec("DELETE FROM $table WHERE id > 50");
            $page = $service->flashcardPage($grammar, 50);
            if ($page['total'] !== 100 || $page['offset'] !== 50 || count($page['cards']) !== 50
                || $page['cards'][0]->id !== 51 || $page['cards'][49]->id !== 100)
                throw new RuntimeException('Count and cards did not share the pre-deletion snapshot');
            $page = $service->flashcardPage($grammar, 50);
            if ($page['total'] !== 50 || $page['offset'] !== 0 || count($page['cards']) !== 50)
                throw new RuntimeException('Next request did not observe the committed deletion');
            for ($id = 51; $id <= 100; $id++) $writer->exec("INSERT INTO $table (id) VALUES ($id)");
            ConcurrentFlashcardStatement::$afterRead = static fn () => $writer->exec("DELETE FROM $table WHERE id > 50");
            $page = $service->flashcardCursor($grammar, 50);
            if ($page['total'] !== 100 || $page['offset'] !== 50 || count($page['cards']) !== 50
                || $page['cards'][0]->id !== 51 || $page['cards'][49]->id !== 100)
                throw new RuntimeException('Cursor count/cards lost the pre-deletion snapshot');
            $page = $service->flashcardCursor($grammar, 50);
            if ($page['total'] !== 50 || $page['offset'] !== 0 || $page['cards'][0]->id !== 1)
                throw new RuntimeException('Cursor did not wrap after concurrent deletion');
        }
    })($path);
}
finally
{
    ConcurrentFlashcardStatement::$afterRead = null;
    gc_collect_cycles();
    foreach ([$path, $path . '-wal', $path . '-shm'] as $file) if (is_file($file)) unlink($file);
}
echo "PASS: both flashcard decks keep count/cards coherent across a concurrent committed deletion.\n";
