<?php

declare(strict_types=1);

use App\Repositories\Manga\MangaCollectionRepository;
use Framework\Database\Database;

require dirname(__DIR__, 2) . '/phpstan-bootstrap.php';

final class FilterQueryCounter extends PDOStatement
{
    public static int $executions = 0;
    public function execute(?array $params = null): bool
    {
        self::$executions++;
        return parent::execute($params);
    }
}
$db = (new ReflectionClass(Database::class))->newInstanceWithoutConstructor();
(new ReflectionMethod(PDO::class, '__construct'))->invoke($db, 'sqlite::memory:');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
$db->setAttribute(PDO::ATTR_STATEMENT_CLASS, [FilterQueryCounter::class]);
$db->exec('CREATE TABLE manga (id INT, slug TEXT, numero INT, livre TEXT, thumbnail TEXT DEFAULT \'\', extension TEXT DEFAULT \'\', statut TEXT DEFAULT \'en_cours\', note INT, lu INT)');
$db->exec("INSERT INTO manga (id, slug, numero, livre, note, lu) VALUES
    (1, 'alpha', 3, 'Alpha', 8, 0), (2, 'alpha', 2, 'Alpha', 10, 1),
    (3, 'beta', 1, 'Beta', 10, 1),
    (4, 'gamma', 2, 'Gamma', NULL, 0), (5, 'gamma', 2, 'Gamma', 4, 1),
    (6, 'delta', 1, 'Delta', 6, 1)");
$repository = new MangaCollectionRepository($db);
$assert = static function (bool $condition, string $message): void {
    if (! $condition) throw new RuntimeException($message);
};
foreach ([true, false] as $notes)
{
    $expectedIds = $notes ? [4, 6, 2] : [2, 4];
    foreach ([intdiv(PHP_INT_MAX, 8) + 2, PHP_INT_MAX] as $extremePage)
    {
        $extreme = $repository->filteredPage($notes, 8, $extremePage);
        $assert($extreme === ['mangas' => [], 'total' => count($expectedIds)], 'Extreme page overflowed or lost the total');
    }
    foreach ([1, 2, 3, 99] as $page)
    {
        FilterQueryCounter::$executions = 0;
        $result = $repository->filteredPage($notes, 2, $page);
        $assert(FilterQueryCounter::$executions === 1, 'Filtered page used more than one query');
        $assert($result['total'] === count($expectedIds), 'Lost count on populated or out-of-range page');
        $assert(array_column($result['mangas'], 'id') === array_slice($expectedIds, ($page - 1) * 2, 2), 'Wrong filter, representative, ordering or pagination');
    }
}
$first = $repository->filteredPage(true, 2, 1)['mangas'][0];
$assert($first->average_note === 2.0 && $first->total === 2 && $first->total_lu === 1, 'Null notes or reading totals changed');
$db->exec('DELETE FROM manga');
foreach ([true, false] as $notes)
{
    $assert($repository->filteredPage($notes, 2, 1) === ['mangas' => [], 'total' => 0], 'Empty collection failed');
}
echo "PASS: single-query filtered pages, null notes, missing/duplicate first volumes, ordering and empty/out-of-range pages.\n";
