<?php

declare(strict_types=1);

final readonly class MigrationRunner
{
    public function __construct(private PDO $db, private string $directory) {}

    public function run(string $action = 'status', ?string $baseline = null): void
    {
        if (!in_array($action, ['status', 'apply', 'baseline'], true)) throw new InvalidArgumentException('Unknown migration action.');
        $database = (string) $this->db->query('SELECT DATABASE()')->fetchColumn();
        if ($database === '') throw new RuntimeException('No database selected.');
        $lock = 'lolissr.migrations.' . substr(hash('sha256', $database), 0, 40);
        $statement = $this->db->prepare('SELECT GET_LOCK(?, 0)');
        $statement->execute([$lock]);
        if ((int) $statement->fetchColumn() !== 1) throw new RuntimeException('Another migration process holds the lock.');
        try
        {
            $files = glob($this->directory . '/*.sql');
            if ($files === false) throw new RuntimeException('Cannot list migrations.');
            sort($files, SORT_STRING);
            // The two historical migrations share a date but have an explicit dependency.
            $historical = ['2026-10-04-schema-consistency.sql', '2026-10-04-user-ownership.sql'];
            usort($files, static function (string $a, string $b) use ($historical): int
            {
                $rank = static fn (string $file): int => array_search(basename($file), $historical, true) === false ? 2 : (int) array_search(basename($file), $historical, true);
                return [$rank($a), basename($a)] <=> [$rank($b), basename($b)];
            });
            $sources = [];
            foreach ($files as $file)
            {
                $sql = file_get_contents($file);
                if ($sql === false || trim($sql) === '') throw new RuntimeException('Empty/unreadable migration: ' . basename($file));
                if (preg_match('/^\s*DELIMITER\b/im', $sql)) throw new RuntimeException('DELIMITER is not supported: ' . basename($file));
                $sources[basename($file)] = ['sql' => $sql, 'checksum' => hash('sha256', str_replace("\r\n", "\n", $sql))];
            }
            $exists = $this->db->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'schema_migrations'")->fetchColumn();
            if ($action !== 'status' && !(bool) $exists)
            {
                $this->db->exec("CREATE TABLE schema_migrations (
                    name VARCHAR(255) CHARACTER SET ascii COLLATE ascii_bin PRIMARY KEY,
                    checksum CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
                    status VARCHAR(20) NOT NULL,
                    started_at DATETIME NOT NULL,
                    finished_at DATETIME NULL,
                    error TEXT NULL
                ) ENGINE=InnoDB");
                $exists = true;
            }
            $records = [];
            if ((bool) $exists)
                foreach ($this->db->query('SELECT * FROM schema_migrations')->fetchAll(PDO::FETCH_ASSOC) as $row) $records[$row['name']] = $row;
            foreach ($records as $name => $record)
            {
                if (!isset($sources[$name]))
                {
                    if (!in_array($record['status'], ['applied', 'baseline'], true)) throw new RuntimeException('Unresolved migration file missing: ' . $name);
                    if ($action === 'status') echo $name . ': ' . $record['status'] . ' (archived)' . PHP_EOL;
                    continue;
                }
                if (!hash_equals($record['checksum'], $sources[$name]['checksum'])) throw new RuntimeException('Recorded migration modified: ' . $name);
            }
            if ($action === 'baseline' && ($baseline === null || !isset($sources[$baseline]))) throw new InvalidArgumentException('Baseline requires an exact migration filename.');
            echo 'Database: ' . $database . PHP_EOL;
            foreach ($sources as $name => $source)
            {
                $status = $records[$name]['status'] ?? 'pending';
                if ($action === 'status') { echo $name . ': ' . $status . PHP_EOL; continue; }
                if ($action === 'baseline')
                {
                    if ($name !== $baseline)
                    {
                        if (!in_array($status, ['applied', 'baseline'], true)) throw new RuntimeException('Resolve earlier migration first: ' . $name);
                        continue;
                    }
                    $statement = $this->db->prepare("INSERT INTO schema_migrations (name, checksum, status, started_at, finished_at) VALUES (?, ?, 'baseline', UTC_TIMESTAMP(), UTC_TIMESTAMP()) ON DUPLICATE KEY UPDATE status = 'baseline', finished_at = UTC_TIMESTAMP(), error = NULL");
                    $statement->execute([$name, $source['checksum']]);
                    echo $name . ': recorded as already applied (SQL not executed)' . PHP_EOL;
                    return;
                }
                if (in_array($status, ['applied', 'baseline'], true)) { echo $name . ': skipped' . PHP_EOL; continue; }
                if ($status !== 'pending') throw new RuntimeException('Interrupted/failed migration requires manual inspection and repair: ' . $name . '. Do not blindly rerun its SQL.');
                $statement = $this->db->prepare("INSERT INTO schema_migrations (name, checksum, status, started_at) VALUES (?, ?, 'running', UTC_TIMESTAMP())");
                $statement->execute([$name, $source['checksum']]);
                try
                {
                    $this->db->exec($source['sql']);
                    if ($this->db->inTransaction()) throw new RuntimeException('Migration left a transaction open.');
                    $statement = $this->db->prepare("UPDATE schema_migrations SET status = 'applied', finished_at = UTC_TIMESTAMP() WHERE name = ?");
                    $statement->execute([$name]);
                    echo $name . ': applied' . PHP_EOL;
                }
                catch (Throwable $error)
                {
                    if ($this->db->inTransaction()) $this->db->rollBack();
                    $statement = $this->db->prepare("UPDATE schema_migrations SET status = 'failed', finished_at = UTC_TIMESTAMP(), error = ? WHERE name = ?");
                    $statement->execute([substr($error->getMessage(), 0, 4000), $name]);
                    throw new RuntimeException('Migration failed; earlier DDL may persist: ' . $name, previous: $error);
                }
            }
        }
        finally
        {
            $statement = $this->db->prepare('SELECT RELEASE_LOCK(?)');
            $statement->execute([$lock]);
        }
    }
}
