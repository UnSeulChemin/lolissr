<?php

declare(strict_types=1);
require dirname(__DIR__, 2) . '/tests/Support/bootstrap.php';

use Framework\Cache\Cache;
use Framework\Config\Env;

if (($argv[1] ?? '') === 'lock')
{
    $lock = fopen($argv[2], 'c');
    if (! flock($lock, LOCK_EX)) exit(1);
    echo "locked\n";
    fflush(STDOUT);
    fgets(STDIN);
    flock($lock, LOCK_UN);
    fclose($lock);
    exit(0);
}

$check = static function (bool $condition, string $message): void {
    if (! $condition) throw new RuntimeException($message);
};
$directory = sys_get_temp_dir() . '/cache-contention-' . bin2hex(random_bytes(8));
mkdir($directory);
(new ReflectionProperty(Cache::class, 'directory'))->setValue(null, $directory);
Env::set('CACHE_ENABLED', true);
Env::set('LOG_ENABLED', false);
$path = $directory . '/' . sha1('busy') . '.cache';
file_put_contents($path, '{"expires_at":1,"value":"expired"}');
$process = proc_open([PHP_BINARY, __FILE__, 'lock', $path . '.metadata.lock'], [
    0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w'],
], $pipes);
$check(is_resource($process), 'Cannot start lock holder.');
try
{
    $check(trim(fgets($pipes[1])) === 'locked', 'Lock holder not ready.');
    $start = hrtime(true);
    $check(Cache::remember('busy', 60, static fn () => 'fresh') === 'fresh', 'Contended read did not recompute.');
    $check((hrtime(true) - $start) / 1e9 < 1, 'Optional metadata access blocked.');
    $check(str_contains(file_get_contents($path), 'expired'), 'Contended read published without a metadata lock.');

    $calls = 0;
    $compute = static function () use (&$calls): string { $calls++; return 'independent'; };
    Cache::remember('other', 60, $compute);
    Cache::remember('other', 60, $compute);
    $check($calls === 1, 'Unrelated key could not publish under contention.');
    try
    {
        Cache::forget('busy');
        throw new LogicException('Invalidation silently ignored contention.');
    }
    catch (RuntimeException $error)
    {
        $check(str_contains($error->getMessage(), 'invalidation lock timed out'), 'Unexpected invalidation failure.');
    }
}
finally
{
    fwrite($pipes[0], "release\n");
    fclose($pipes[0]);
    stream_get_contents($pipes[1]);
    $error = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    $status = proc_close($process);
    foreach (glob($directory . '/*') ?: [] as $file) unlink($file);
    rmdir($directory);
}
$check($status === 0 && $error === '', 'Lock holder failed: ' . $error);
echo "PASS: contended reads recompute without publishing; independent keys progress; invalidation remains strict.\n";
