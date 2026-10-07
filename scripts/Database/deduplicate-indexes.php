<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli')
{ http_response_code(404); exit; }
if (count($argv) > 2 || (isset($argv[1]) && !in_array($argv[1], ['--apply', '--check'], true)))
    throw new InvalidArgumentException('Usage: php scripts/Database/deduplicate-indexes.php [--apply|--check]');
define('ROOT', dirname(__DIR__, 2));
require ROOT . '/vendor/autoload.php';
require ROOT . '/Framework/Support/Helpers.php';
\Framework\Application\Bootstrap::loadEnvOnly();
$database = new \Framework\Database\Database();
$apply = ($argv[1] ?? '') === '--apply';
// Keep the named uniqueness constraints. DDL commits implicitly: validate all
// candidates before applying any changes, and allow safe repeated execution.
$candidates = [
    ['manga', 'idx_slug_numero', 'uq_manga_slug_numero'],
    ['artbook', 'slug_numero', 'uq_artbook_slug_numero'],
    ['figurine', 'unique_slug_numero', 'uq_figurine_slug_numero'],
    ['nendoroid', 'unique_slug_numero', 'uq_nendoroid_slug_numero'],
    ['peluche', 'unique_peluche', 'uq_peluche_slug_numero']
];
$plan = [];
foreach ($candidates as [$table, $drop, $keep])
{
    $indexes = [];
    foreach ($database->query("SHOW INDEX FROM `$table`")->fetchAll(PDO::FETCH_ASSOC) as $row)
        $indexes[$row['Key_name']][(int)$row['Seq_in_index']] = $row;
    if (!isset($indexes[$drop]))
    { echo "Already clean: $table\n"; continue; }
    if (!isset($indexes[$keep])) throw new RuntimeException('Missing retained index: ' . $keep);
    $signature = static function (array $rows): array
    {
        ksort($rows);
        return array_map(static fn (array $row): array => [
            $row['Column_name'], $row['Sub_part'], $row['Collation'], $row['Index_type'],
            $row['Expression'] ?? null, $row['Visible'] ?? 'YES', $row['Ignored'] ?? 'NO'
        ], array_values($rows));
    };
    if ($signature($indexes[$drop]) !== $signature($indexes[$keep]) || (int)$indexes[$keep][1]['Non_unique'] !== 0)
        throw new RuntimeException('Indexes differ; refusing to drop ' . $drop);
    $plan[] = "ALTER TABLE `$table` DROP INDEX `$drop`";
}
foreach ($plan as $sql)
{
    echo ($apply ? 'APPLY: ' : 'PLAN: ') . $sql . ";\n";
    if ($apply) $database->exec($sql);
}
echo count($plan) . ($apply ? ' redundant indexes removed.' : ' redundant indexes eligible; use --apply to execute.') . "\n";
if (($argv[1] ?? '') === '--check') exit($plan === [] ? 0 : 1);
