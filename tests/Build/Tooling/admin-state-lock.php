<?php
declare(strict_types=1);

if (($argv[1] ?? '') === '--writer')
{
    $handle = fopen($argv[2], 'c');
    if ($handle === false || !flock($handle, LOCK_EX)) throw new RuntimeException('Cannot lock writer fixture');
    try
    {
        ftruncate($handle, 0);
        fwrite($handle, '{"state":');
        fflush($handle);
        file_put_contents($argv[3], 'ready');
        usleep(400000);
        fwrite($handle, '"done","updated":' . time() . '}');
        fflush($handle);
    }
    finally
    { flock($handle, LOCK_UN); fclose($handle); }
    exit;
}

$root = dirname(__DIR__, 3);
$fixture = sys_get_temp_dir() . '/admin-state-' . bin2hex(random_bytes(8));
mkdir($fixture . '/App/Services/Admin', 0700, true);
mkdir($fixture . '/storage/admin-jobs', 0700, true);
try
{
    foreach (['MaintenanceJob' => 'maintenance', 'RecommendationJob' => 'recommendations', 'ReleaseJob' => 'releases'] as $name => $file)
    {
        copy($root . '/App/Services/Admin/' . $name . '.php', $fixture . '/App/Services/Admin/' . $name . '.php');
        require $fixture . '/App/Services/Admin/' . $name . '.php';
        $class = 'App\\Services\\Admin\\' . $name;
        if ($class::status() !== ['state' => 'idle', 'updated' => 0]) throw new RuntimeException('Missing state behavior changed');
        $class::writeState('running');
        $path = $fixture . '/storage/admin-jobs/' . $file . '.json';
        $ready = $fixture . '/ready-' . $file;
        $process = proc_open([PHP_BINARY, __FILE__, '--writer', $path, $ready],
            [0 => ['pipe', 'r'], 1 => ['file', $fixture . '/writer.log', 'a'], 2 => ['file', $fixture . '/writer.log', 'a']], $pipes);
        if (!is_resource($process)) throw new RuntimeException('Cannot start concurrent writer');
        fclose($pipes[0]);
        try
        {
            $deadline = microtime(true) + 5;
            while (!is_file($ready) && microtime(true) < $deadline)
            { clearstatcache(); usleep(1000); }
            if (!is_file($ready)) throw new RuntimeException('Writer did not signal readiness');
            if ($class::status()['state'] !== 'done') throw new RuntimeException('Reader observed an incomplete state: ' . $name);
        }
        finally
        { $code = proc_close($process); }
        if ($code !== 0) throw new RuntimeException('Concurrent writer failed');
        $class::writeState('queued');
        if ($class::status()['state'] !== 'queued') throw new RuntimeException('Reader lock was not released');
        file_put_contents($path, '{invalid');
        if ($class::status()['state'] !== 'idle') throw new RuntimeException('Corrupt state fallback changed');
        echo 'PASS: ' . $name . ' waits for a locked partial write, reads complete state and releases its lock.' . PHP_EOL;
    }
}
finally
{
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($fixture, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
    foreach ($files as $file) $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
    rmdir($fixture);
}
