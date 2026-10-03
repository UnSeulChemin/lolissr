<?php
declare(strict_types=1);
$root = sys_get_temp_dir() . '/build-lock-' . bin2hex(random_bytes(8));
mkdir($root . '/storage', 0755, true);
$lock = fopen($root . '/storage/.build.lock', 'c');
$run = static function () use ($root): int
{
    $process = proc_open([PHP_BINARY, '-r',
        'require $argv[1]; try { BuildLock::acquire($argv[2]); BuildLock::acquire($argv[2]); } catch (RuntimeException $e) { exit(23); }',
        dirname(__DIR__, 2) . '/scripts/Support/BuildLock.php', $root],
        [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
    if (!is_resource($process)) throw new RuntimeException('Cannot start lock test worker.');
    foreach ($pipes as $pipe) fclose($pipe);
    return proc_close($process);
};
try
{
    flock($lock, LOCK_EX);
    if ($run() !== 23) throw new RuntimeException('Concurrent build was not blocked.');
    flock($lock, LOCK_UN);
    if ($run() !== 0 || $run() !== 0) throw new RuntimeException('Nested acquisition or shutdown release failed.');
    echo "PASS: concurrent build rejected, nested acquisition and subsequent builds accepted.\n";
}
finally
{
    fclose($lock);
    unlink($root . '/storage/.build.lock');
    rmdir($root . '/storage');
    rmdir($root);
}
