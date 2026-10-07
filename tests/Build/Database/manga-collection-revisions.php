<?php
declare(strict_types=1);

require dirname(__DIR__, 2) . '/Support/bootstrap.php';
require ROOT . '/scripts/Database/Support/MigrationRunner.php';
\Framework\Application\Bootstrap::loadEnvOnly();
$db = new \Framework\Database\Database();
$original = (string) $db->query('SELECT DATABASE()')->fetchColumn();
$fixture = 'manga_revision_test_' . bin2hex(random_bytes(8));
$directory = sys_get_temp_dir() . '/' . $fixture;
mkdir($directory);
$db->exec("CREATE DATABASE `$fixture`");
$check = static function (bool $ok, string $message): void
{ if (!$ok) throw new RuntimeException($message); };
try
{
    $db->exec("USE `$fixture`");
    $db->exec('CREATE TABLE manga (id INT PRIMARY KEY, user_id INT NOT NULL, slug VARCHAR(255), livre VARCHAR(255), numero INT) ENGINE=InnoDB');
    $db->exec("INSERT INTO manga VALUES (1, 1, 'owned', 'Owned', 1), (2, 1, 'owned', 'Owned', 2), (3, 2, 'other', 'Other', 1)");
    $migration = '2026-10-07-manga-collection-revisions.sql';
    copy(ROOT . '/scripts/Database/migrations/' . $migration, $directory . '/' . $migration);
    $runner = new MigrationRunner($db, $directory);
    $runner->run('apply');
    $runner->run('apply');
    $revision = static fn (int $owner): string => (string) $db->query('SELECT revision FROM manga_collection_revisions WHERE user_id = ' . $owner)->fetchColumn();
    $check($revision(1) !== '' && $revision(2) !== '', 'Existing owners were not initialized');
    $other = $revision(2);
    foreach ([
        "INSERT INTO manga VALUES (4, 1, 'new', 'New', 1)",
        "UPDATE manga SET livre = 'Renamed' WHERE id = 4",
        "UPDATE manga SET slug = 'renamed' WHERE id = 4",
        'UPDATE manga SET numero = 2 WHERE id = 4',
        'DELETE FROM manga WHERE id = 4'
    ] as $sql)
    {
        $before = $revision(1);
        $db->exec($sql);
        $check($revision(1) !== $before && $revision(2) === $other, 'Direct write did not invalidate only its owner');
    }
    $before = $revision(1);
    $db->beginTransaction();
    $db->exec("UPDATE manga SET livre = 'Rollback' WHERE id = 1");
    $check($revision(1) !== $before, 'Transaction did not update revision');
    $db->rollBack();
    $check($revision(1) === $before, 'Rollback did not restore revision');
    $db->exec('UPDATE manga SET user_id = 2 WHERE id = 1');
    $check($revision(1) !== $before && $revision(2) !== $other, 'Ownership transfer did not invalidate both owners');
    $before = $revision(1);
    $db->exec('DELETE FROM manga WHERE user_id = 1');
    $check($revision(1) !== $before, 'Deleting last volume did not invalidate owner');
    echo "PASS: real MySQL revision migration, existing owners, direct inserts/renames/numbers/deletes, owner isolation, transfer and rollback.\n";
}
finally
{
    if ($db->inTransaction()) $db->rollBack();
    $db->exec("USE `$original`");
    $db->exec("DROP DATABASE `$fixture`");
    foreach (glob($directory . '/*') as $file) unlink($file);
    rmdir($directory);
}
