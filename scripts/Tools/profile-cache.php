<?php
declare(strict_types=1);

// The live cache is read only. Optional purge benchmarks use disposable directories.
if (PHP_SAPI !== 'cli')
{ http_response_code(404); exit; }
if (array_diff(array_slice($argv, 1), ['--benchmark']) !== [])
{ fwrite(STDERR, "Usage: composer cache:profile [-- --benchmark]\n"); exit(1); }
define('ROOT', dirname(__DIR__, 2));
require ROOT . '/vendor/autoload.php';
require ROOT . '/Framework/Support/Helpers.php';

$directory = base_path('storage/cache');
$counts = ['values' => 0, 'producer_locks' => 0, 'metadata_locks' => 0, 'versions' => 0, 'temporary' => 0];
$keys = [];
$bytes = 0;
$scanTimes = [];
for ($i = 0; $i < 11; $i++)
{
    $start = hrtime(true);
    $entries = is_dir($directory) ? scandir($directory) : [];
    if ($entries === false) throw new RuntimeException('Cannot list cache directory.');
    if ($i > 0) $scanTimes[] = (hrtime(true) - $start) / 1_000_000;
}
foreach ($entries as $entry)
{
    if (!preg_match('/^([a-f0-9]{40})\.cache($|\.lock$|\.metadata\.lock$|\.version$|\.[a-f0-9]+\.tmp$)/D', $entry, $match)) continue;
    $path = $directory . '/' . $entry;
    if (!is_file($path) || is_link($path)) continue;
    $type = match ($match[2])
    {
        '' => 'values', '.lock' => 'producer_locks', '.metadata.lock' => 'metadata_locks',
        '.version' => 'versions', default => 'temporary'
    };
    $counts[$type]++;
    $keys[$match[1]][$type] = true;
    $bytes += filesize($path) ?: 0;
}
sort($scanTimes);
$withoutValues = count(array_filter($keys, static fn (array $types): bool => !isset($types['values'])));
echo json_encode(['live_cache' => ['keys' => count($keys), 'keys_without_value' => $withoutValues,
    'files' => $counts, 'content_bytes' => $bytes, 'directory_scan_median_ms' => ($scanTimes[4] + $scanTimes[5]) / 2]], JSON_PRETTY_PRINT) . PHP_EOL;
echo "Keys without values may still have active producers; they are not safe deletion candidates. Content bytes exclude filesystem allocation overhead.\n";

$profiles = 0;
$counters = ['cache.lock_timeout' => 0, 'cache.recompute' => 0, 'cache.metadata_timeout' => 0];
$logs = glob(base_path('storage/logs/app-*.log')) ?: [];
sort($logs);
foreach (array_slice($logs, -3) as $log)
{
    $handle = fopen($log, 'rb');
    if ($handle === false) continue;
    try
    {
        while (($line = fgets($handle)) !== false)
        {
            $data = json_decode($line, true);
            if (!is_array($data) || !is_string($data['message'] ?? null) || !str_starts_with($data['message'], '[PROFILER] ')) continue;
            $values = $data['context']['counters'] ?? null;
            if (!is_array($values)) continue;
            $profiles++;
            foreach ($counters as $key => $value) if (is_int($values[$key] ?? null)) $counters[$key] += $values[$key];
        }
    }
    finally
    { fclose($handle); }
}
echo json_encode(['recent_profiler_logs' => ['files_scanned' => min(3, count($logs)), 'recorded_requests' => $profiles,
    'counters' => $counters]], JSON_PRETTY_PRINT) . PHP_EOL;
if ($profiles === 0) echo "No recorded profiler requests: no conclusion about live contention.\n";

if (!in_array('--benchmark', $argv, true)) exit;
$cacheDirectory = new ReflectionProperty(\Framework\Cache\Cache::class, 'directory');
$previous = $cacheDirectory->getValue();
foreach (array_unique([count($keys), 1000]) as $size)
{
    $fixture = sys_get_temp_dir() . '/cache-profile-' . bin2hex(random_bytes(8));
    mkdir($fixture, 0700);
    try
    {
        $cacheDirectory->setValue(null, $fixture);
        foreach (range(1, max(1, $size)) as $i)
        {
            if ($size === 0) break;
            $path = $fixture . '/' . sha1('fixture-' . $i) . '.cache';
            foreach (['' => '{}', '.lock' => '', '.metadata.lock' => '', '.version' => 'fixture-generation'] as $suffix => $contents)
                file_put_contents($path . $suffix, $contents);
        }
        $start = hrtime(true);
        $deleted = \Framework\Cache\Cache::clear();
        $purgeTime = (hrtime(true) - $start) / 1_000_000;
        if ($deleted !== $size) throw new RuntimeException('Purge benchmark removed an unexpected number of values.');
        $remaining = count(scandir($fixture)) - 2;
        if ($remaining !== $size * 3) throw new RuntimeException('Purge benchmark changed stable metadata.');
        echo json_encode(['isolated_purge' => ['synthetic_keys' => $size, 'deleted_values' => $deleted,
            'retained_metadata_files' => $remaining, 'elapsed_ms' => $purgeTime]]) . PHP_EOL;
    }
    finally
    {
        $cacheDirectory->setValue(null, $previous);
        foreach (scandir($fixture) ?: [] as $file) if ($file !== '.' && $file !== '..') unlink($fixture . '/' . $file);
        rmdir($fixture);
    }
}
