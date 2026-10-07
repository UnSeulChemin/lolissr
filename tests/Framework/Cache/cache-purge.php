<?php
declare(strict_types=1);
require dirname(__DIR__, 3) . '/tests/Support/bootstrap.php';

use Framework\Cache\Cache;
use Framework\Config\Environment;

$directory = $argv[2] ?? sys_get_temp_dir() . '/cache-purge-' . bin2hex(random_bytes(8));
if (! is_dir($directory)) mkdir($directory);
$property = new ReflectionProperty(Cache::class, 'directory');
$previous = $property->getValue();
$property->setValue(null, $directory);
Environment::set('CACHE_ENABLED', true);
Environment::set('LOG_ENABLED', false);
$path = $directory . '/' . sha1('running') . '.cache';
if (($argv[1] ?? '') === 'producer')
{
    Cache::remember('running', 60, static function (): string
    {
        echo "ready\n";
        fflush(STDOUT);
        fgets(STDIN);
        return 'stale';
    });
    exit(0);
}
try
{
    Cache::remember('running', 60, static function () use ($directory): string
    {
        // Interleaving: the purge finishes while the producer is still computing.
        Cache::clear();
        return 'before-purge';
    });
    if (is_file($path)) throw new RuntimeException('Producer published after purge.');
    if (! is_file($path . '.lock') || ! is_file($path . '.metadata.lock')) throw new RuntimeException('Stable locks removed.');
    Cache::remember('running', 60, static fn () => 'after-purge');
    if (Cache::remember('running', 60, static fn () => 'wrong') !== 'after-purge') throw new RuntimeException('Fresh producer cannot publish.');
    Cache::clear();
    if (is_file($path)) throw new RuntimeException('Published entry not removed.');
    $process = proc_open([PHP_BINARY, __FILE__, 'producer', $directory], [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
    if (! is_resource($process)) throw new RuntimeException('Cannot start producer.');
    try
    {
        if (trim(fgets($pipes[1])) !== 'ready') throw new RuntimeException('Producer not ready.');
        Cache::clear();
    }
    finally
    {
        fwrite($pipes[0], "continue\n");
        fclose($pipes[0]);
        stream_get_contents($pipes[1]);
        $error = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        if (proc_close($process) !== 0) throw new RuntimeException($error);
    }
    if (is_file($path)) throw new RuntimeException('Concurrent producer published after purge.');
    $fixture = $directory . '/runtime';
    foreach (['scripts/Maintenance', 'vendor', 'Framework/Support', 'storage/cache/nested'] as $child) mkdir($fixture . '/' . $child, 0777, true);
    copy(ROOT . '/scripts/Maintenance/clear-runtime.php', $fixture . '/scripts/Maintenance/clear-runtime.php');
    copy(ROOT . '/Framework/Support/Helpers.php', $fixture . '/Framework/Support/Helpers.php');
    file_put_contents($fixture . '/vendor/autoload.php', '<?php require ' . var_export(ROOT . '/vendor/autoload.php', true) . ';');
    $fixtureCache = $fixture . '/storage/cache';
    file_put_contents($fixtureCache . '/.gitkeep', '');
    file_put_contents($fixtureCache . '/bootstrap.php', 'obsolete');
    file_put_contents($fixtureCache . '/nested/catalog.json', '{}');
    $entry = $fixtureCache . '/' . sha1('fixture') . '.cache';
    file_put_contents($entry, '{}');
    file_put_contents($entry . '.lock', '');
    $process = proc_open([PHP_BINARY, $fixture . '/scripts/Maintenance/clear-runtime.php', 'cache'], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
    if (! is_resource($process)) throw new RuntimeException('Cannot start purge command.');
    $output = stream_get_contents($pipes[1]);
    $error = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    if (proc_close($process) !== 0) throw new RuntimeException($output . $error);
    if (is_file($entry) || is_file($fixtureCache . '/bootstrap.php') || is_dir($fixtureCache . '/nested')) throw new RuntimeException('Runtime caches not removed.');
    if (! is_file($entry . '.lock') || ! is_file($entry . '.version') || ! is_file($fixtureCache . '/.gitkeep')) throw new RuntimeException('Purge removed protected files.');
    echo "PASS: purge discards an in-flight producer, preserves locks, removes entries and allows fresh publication.\n";
}
finally
{
    $property->setValue(null, $previous);
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
    foreach ($iterator as $file)
    {
        if ($file->isDir()) rmdir($file->getPathname());
        else unlink($file->getPathname());
    }
    rmdir($directory);
}
