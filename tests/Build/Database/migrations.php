<?php

declare(strict_types=1);

require dirname(__DIR__, 2) . '/Support/bootstrap.php';
require ROOT . '/scripts/Database/Support/MigrationRunner.php';
\Framework\Application\Bootstrap::loadEnvOnly();
$db = new \Framework\Database\Database();
$other = new \Framework\Database\Database();
$original = (string) $db->query('SELECT DATABASE()')->fetchColumn();
$fixture = 'migration_test_' . bin2hex(random_bytes(8));
$directory = sys_get_temp_dir() . '/' . $fixture;
mkdir($directory);
$check = static function (bool $ok, string $message): void
{ if (!$ok) throw new RuntimeException($message); };
$reject = static function (callable $action, string $expected): void
{
    try
    { $action(); }
    catch (Throwable $error)
    {
        if (!str_contains($error->getMessage(), $expected)) throw $error;
        return;
    }
    throw new RuntimeException('Expected rejection: ' . $expected);
};
$db->exec("CREATE DATABASE `$fixture`");
try
{
    $db->exec("USE `$fixture`");
    $other->exec("USE `$fixture`");
    $runner = new MigrationRunner($db, $directory);
    $runner->run('status');
    $check((int) $db->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE()")->fetchColumn() === 0, 'Status changed schema');
    file_put_contents($directory . '/2026-10-04-schema-consistency.sql', "CREATE TABLE fixture (id INT PRIMARY KEY); INSERT INTO fixture VALUES (1);");
    file_put_contents($directory . '/2026-10-04-user-ownership.sql', 'ALTER TABLE fixture ADD COLUMN owner INT DEFAULT 1;');
    $runner->run('apply');
    $runner->run('apply');
    $check((int) $db->query('SELECT COUNT(*) FROM fixture')->fetchColumn() === 1, 'Multiple statements or repeated apply failed');
    $check((int) $db->query('SELECT owner FROM fixture')->fetchColumn() === 1, 'Historical dependency order failed');
    $lock = 'lolissr.migrations.' . substr(hash('sha256', $fixture), 0, 40);
    $statement = $other->prepare('SELECT GET_LOCK(?, 0)');
    $statement->execute([$lock]);
    $reject(fn () => $runner->run('apply'), 'holds the lock');
    $statement = $other->prepare('SELECT RELEASE_LOCK(?)');
    $statement->execute([$lock]);
    $first = $directory . '/2026-10-04-schema-consistency.sql';
    $source = file_get_contents($first);
    file_put_contents($first, $source . ' ');
    $reject(fn () => $runner->run('apply'), 'modified');
    file_put_contents($first, $source);
    file_put_contents($directory . '/2026-10-05-failure.sql', 'ALTER TABLE fixture ADD COLUMN partial INT; INSERT INTO missing_table VALUES (1);');
    $reject(fn () => $runner->run('apply'), 'earlier DDL may persist');
    $check($db->query('SELECT partial FROM fixture')->fetchColumn() === null, 'Partial DDL not exercised');
    $check($db->query("SELECT status FROM schema_migrations WHERE name = '2026-10-05-failure.sql'")->fetchColumn() === 'failed', 'Failure not recorded');
    $reject(fn () => $runner->run('apply'), 'manual inspection');
    // Simulate manual completion of the failed migration before accepting its state.
    $db->exec('CREATE TABLE missing_table (id INT); INSERT INTO missing_table VALUES (1)');
    $runner->run('baseline', '2026-10-05-failure.sql');
    file_put_contents($directory . '/2026-10-06-baseline.sql', 'THIS SQL MUST NOT EXECUTE');
    $runner->run('baseline', '2026-10-06-baseline.sql');
    $runner->run('apply');
    $check((int) $db->query('SELECT COUNT(*) FROM schema_migrations')->fetchColumn() === 4, 'Baseline tracking failed');
    unlink($directory . '/2026-10-04-schema-consistency.sql');
    $runner->run('apply');
    $runner->run('status');
    $db->exec("UPDATE schema_migrations SET status = 'failed' WHERE name = '2026-10-04-schema-consistency.sql'");
    $reject(fn () => $runner->run('apply'), 'Unresolved migration file missing');
    echo "PASS: migration ordering, multi-statement SQL, idempotence, checksum protection, read-only status, lock contention, partial DDL failure and explicit baseline. Disposable database only.\n";
}
finally
{
    $other->exec('USE `' . str_replace('`', '``', $original) . '`');
    $db->exec('USE `' . str_replace('`', '``', $original) . '`');
    $db->exec("DROP DATABASE `$fixture`");
    foreach (glob($directory . '/*') ?: [] as $file) unlink($file);
    rmdir($directory);
}
