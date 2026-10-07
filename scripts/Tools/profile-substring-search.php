<?php
declare(strict_types=1);

// Synthetic SQL benchmark: temporary tables only, no changes to application data/indexes.
if (PHP_SAPI !== 'cli')
{ http_response_code(404); exit; }
define('ROOT', dirname(__DIR__, 2));
require ROOT . '/vendor/autoload.php';
require ROOT . '/Framework/Support/Helpers.php';
\Framework\Application\Bootstrap::loadEnvOnly();
$db = new \Framework\Database\Database();
foreach (['manga', 'artbook', 'figurine', 'nendoroid', 'peluche', 'chinois_grammaire', 'chinois_vocabulaire'] as $table)
{
    $indexes = [];
    foreach ($db->query("SHOW INDEX FROM `$table`")->fetchAll(PDO::FETCH_ASSOC) as $row)
        $indexes[$row['Key_name']][] = $row['Column_name'];
    echo $table . ' indexes: ' . json_encode($indexes, JSON_UNESCAPED_UNICODE) . PHP_EOL;
}
$name = 'substring_profile_' . bin2hex(random_bytes(8));
echo "Synthetic fixture: actual figurine schema/indexes/collation, 20,000 rows, full search projection, 11 samples (first excluded), LIMIT 5.\n";
try
{
    $db->exec("CREATE TEMPORARY TABLE `$name` LIKE figurine");
    for ($batch = 0; $batch < 40; $batch++)
    {
        $values = [];
        for ($offset = 1; $offset <= 500; $offset++)
        {
            $id = $batch * 500 + $offset;
            $title = sprintf('Character %08d', $id);
            $origin = $id % 997 === 0 ? 'Rare series' : 'Ordinary series';
            $values[] = "($id, " . (1 + $id % 20) . ", 'item-$id', 1, '$origin', '$title', 'fixture-$id', 'jpg', '1/7', 'Fixture')";
        }
        $db->exec("INSERT INTO `$name` (id, user_id, slug, numero, origin, waifu, thumbnail, extension, scale, company) VALUES " . implode(',', $values));
    }
    foreach (['20 owners / 1,000 rows each', '1 owner / 20,000 rows'] as $scenario)
    {
        if (str_starts_with($scenario, '1 owner')) $db->exec("UPDATE `$name` SET user_id = 1");
        $expected = [];
        foreach (['baseline', 'owner_search_order'] as $index)
        {
            if ($index === 'owner_search_order')
                $db->exec("ALTER TABLE `$name` ADD INDEX owner_search_order (user_id, origin, waifu, numero, id)");
            // Compare the optimizer's actual choice as well as the candidate's best-case forced plan.
            foreach ($index === 'baseline' ? ['automatic'] : ['automatic', 'forced'] as $planMode)
            {
              foreach (['series', 'rare', 'absent'] as $query)
              {
                $hint = $planMode === 'forced' ? 'FORCE INDEX (`owner_search_order`)' : '';
                $sql = "SELECT slug, numero, origin, waifu, thumbnail, extension FROM (SELECT * FROM `$name` $hint WHERE user_id = 1) owned
                    WHERE (waifu LIKE ? OR origin LIKE ? OR slug LIKE ?)
                    ORDER BY origin, waifu, numero, id LIMIT 5";
                $statement = $db->prepare($sql);
                $samples = [];
                for ($iteration = 0; $iteration < 11; $iteration++)
                {
                    $start = hrtime(true);
                    $statement->execute(array_fill(0, 3, '%' . $query . '%'));
                    $rows = $statement->fetchAll(PDO::FETCH_ASSOC);
                    if ($iteration > 0) $samples[] = (hrtime(true) - $start) / 1_000_000;
                }
                if ($index === 'baseline') $expected[$query] = $rows;
                elseif ($rows !== $expected[$query]) throw new RuntimeException('Candidate index changed ordered results');
                sort($samples);
                $plan = $db->prepare('EXPLAIN ' . $sql);
                $plan->execute(array_fill(0, 3, '%' . $query . '%'));
                $plans = array_map(static fn (array $row): array => array_intersect_key($row,
                    array_flip(['type', 'key', 'rows', 'Extra'])), $plan->fetchAll(PDO::FETCH_ASSOC));
                printf("%s | %s/%s | %s: median %.3f ms, %d results, %s\n", $scenario, $index, $planMode, $query,
                    ($samples[4] + $samples[5]) / 2, count($rows), json_encode($plans));
              }
            }
            if ($index === 'owner_search_order') $db->exec("ALTER TABLE `$name` DROP INDEX owner_search_order");
        }
    }
}
finally
{
    $db->exec("DROP TEMPORARY TABLE IF EXISTS `$name`");
}
