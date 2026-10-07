<?php
declare(strict_types=1);

require dirname(__DIR__, 2) . '/Support/bootstrap.php';
require ROOT . '/scripts/Database/Support/MigrationRunner.php';
\Framework\Application\Bootstrap::loadEnvOnly();
$db = new \Framework\Database\Database();
$reader = new \Framework\Database\Database();
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
    $reader->exec("USE `$fixture`");
    $db->exec('CREATE TABLE manga (id INT PRIMARY KEY, user_id INT NOT NULL, slug VARCHAR(255), livre VARCHAR(255), numero INT) ENGINE=InnoDB');
    $db->exec("INSERT INTO manga VALUES (1, 1, 'owned', 'Owned', 1), (2, 1, 'owned', 'Owned', 2), (3, 2, 'other', 'Other', 1)");
    $migration = '2026-10-07-manga-collection-revisions.sql';
    copy(ROOT . '/scripts/Database/migrations/' . $migration, $directory . '/' . $migration);
    $runner = new MigrationRunner($db, $directory);
    $runner->run('apply');
    $runner->run('apply');
    $revision = static fn (int $owner): string => (string) $db->query('SELECT revision FROM manga_collection_revisions WHERE user_id = ' . $owner)->fetchColumn();
    $check($revision(1) !== '' && $revision(2) !== '', 'Existing owners were not initialized');
    $db->exec('ALTER TABLE manga ADD COLUMN lu INT DEFAULT 0, ADD COLUMN note INT DEFAULT 0, ADD COLUMN commentaire TEXT, ADD COLUMN xp_read_rewarded INT DEFAULT 0');
    $measure = static function () use ($db, $revision): array
    {
        $changed = 0;
        $samples = [];
        for ($iteration = 0; $iteration < 100; $iteration++)
        {
            $before = $revision(1);
            $start = hrtime(true);
            $db->exec('UPDATE manga SET note = ' . ($iteration % 11) . ' WHERE id = 1');
            $samples[] = (hrtime(true) - $start) / 1_000_000;
            if ($revision(1) !== $before) $changed++;
        }
        sort($samples);
        return ['invalidations' => $changed, 'median' => ($samples[49] + $samples[50]) / 2];
    };
    $oldMeasure = $measure();
    $selective = '2026-10-07-manga-selective-revisions.sql';
    copy(ROOT . '/scripts/Database/migrations/' . $selective, $directory . '/' . $selective);
    $runner->run('apply');
    $runner->run('apply');
    $newMeasure = $measure();
    $check($oldMeasure['invalidations'] === 100 && $newMeasure['invalidations'] === 0, 'Unrelated writes still invalidate recommendations');
    printf("100 note updates: invalidations %d -> %d; median SQL %.3f -> %.3f ms (local fixture).\n",
        $oldMeasure['invalidations'], $newMeasure['invalidations'], $oldMeasure['median'], $newMeasure['median']);
    $before = $revision(1);
    foreach (["UPDATE manga SET lu = 1, commentaire = 'Updated', xp_read_rewarded = 1 WHERE id = 1",
        'UPDATE manga SET livre = livre, slug = slug, numero = numero WHERE id = 1'] as $sql)
    {
        $db->exec($sql);
        $check($revision(1) === $before, 'Unrelated or no-op update changed revision');
    }
    foreach (["UPDATE manga SET livre = 'owned' WHERE id = 1", "UPDATE manga SET livre = 'owned ' WHERE id = 1",
        'UPDATE manga SET livre = NULL WHERE id = 1', "UPDATE manga SET livre = 'Owned' WHERE id = 1",
        "UPDATE manga SET slug = 'OWNED' WHERE id = 1", "UPDATE manga SET slug = 'owned' WHERE id = 1"] as $sql)
    {
        $before = $revision(1);
        $db->exec($sql);
        $check($revision(1) !== $before, 'Exact title/slug change did not invalidate revision');
    }
    $other = $revision(2);
    foreach ([
        "INSERT INTO manga (id, user_id, slug, livre, numero) VALUES (4, 1, 'new', 'New', 1)",
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
    $check((string) $reader->query('SELECT revision FROM manga_collection_revisions WHERE user_id = 1')->fetchColumn() === $before,
        'Concurrent reader observed an uncommitted revision');
    $db->rollBack();
    $check($revision(1) === $before, 'Rollback did not restore revision');
    $db->beginTransaction();
    $db->exec("UPDATE manga SET livre = 'Committed' WHERE id = 1");
    $committed = $revision(1);
    $db->commit();
    $check((string) $reader->query('SELECT revision FROM manga_collection_revisions WHERE user_id = 1')->fetchColumn() === $committed,
        'Concurrent reader missed a committed revision');
    $before = $committed;
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
    $reader->exec("USE `$original`");
    $db->exec("USE `$original`");
    $db->exec("DROP DATABASE `$fixture`");
    foreach (glob($directory . '/*') as $file) unlink($file);
    rmdir($directory);
}
