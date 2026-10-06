<?php
declare(strict_types=1);
$root = sys_get_temp_dir() . '/build-lock-' . bin2hex(random_bytes(8));
mkdir($root . '/storage', 0755, true);
$lock = fopen($root . '/storage/.build.lock', 'c');
$run = static function () use ($root): int
{
    $process = proc_open([PHP_BINARY, '-r',
        'require $argv[1]; try { BuildLock::acquire($argv[2]); BuildLock::acquire($argv[2]); } catch (RuntimeException $e) { exit(23); }',
        dirname(__DIR__, 3) . '/scripts/Support/BuildLock.php', $root],
        [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
    if (!is_resource($process)) throw new RuntimeException('Cannot start lock test worker.');
    foreach ($pipes as $pipe) fclose($pipe);
    return proc_close($process);
};
try
{
    flock($lock, LOCK_EX);
    if ($run() !== 23) throw new RuntimeException('Concurrent build was not blocked.');
    mkdir($root . '/scripts/Assets/Images', 0755, true);
    mkdir($root . '/scripts/Support', 0755, true);
    copy(dirname(__DIR__, 3) . '/scripts/Support/BuildLock.php', $root . '/scripts/Support/BuildLock.php');
    foreach (['build-profile-images.php', 'build-profile-manifest.php'] as $script)
    {
        $fixture = $root . '/scripts/Assets/Images/' . $script;
        copy(dirname(__DIR__, 3) . '/scripts/Assets/Images/' . $script, $fixture);
        $process = proc_open([PHP_BINARY, $fixture],
            [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
        if (!is_resource($process)) throw new RuntimeException('Cannot start profile build worker.');
        fclose($pipes[0]);
        $output = stream_get_contents($pipes[1]);
        $error = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $exit = proc_close($process);
        if ($exit === 0 || !str_contains($output . $error, 'Another build or asset cleanup is running.'))
            throw new RuntimeException($script . ' did not reject contention before loading builders or publishing files.');
    }
    flock($lock, LOCK_UN);
    if ($run() !== 0 || $run() !== 0) throw new RuntimeException('Nested acquisition or shutdown release failed.');
    echo "PASS: concurrent build and profile scripts rejected, nested acquisition and subsequent builds accepted.\n";
}
finally
{
    fclose($lock);
    foreach (['build-profile-images.php', 'build-profile-manifest.php'] as $script)
        if (is_file($root . '/scripts/Assets/Images/' . $script)) unlink($root . '/scripts/Assets/Images/' . $script);
    if (is_file($root . '/scripts/Support/BuildLock.php')) unlink($root . '/scripts/Support/BuildLock.php');
    foreach (['scripts/Assets/Images', 'scripts/Assets', 'scripts/Support', 'scripts'] as $directory)
        if (is_dir($root . '/' . $directory)) rmdir($root . '/' . $directory);
    unlink($root . '/storage/.build.lock');
    rmdir($root . '/storage');
    rmdir($root);
}
