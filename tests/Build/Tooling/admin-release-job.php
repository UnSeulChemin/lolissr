<?php
declare(strict_types=1);

// Run the real launcher and worker in an isolated project, with a fake sync.
$source = dirname(__DIR__, 3);
$fixture = sys_get_temp_dir() . '/admin-job-' . bin2hex(random_bytes(8));
foreach (['App/Services/Admin', 'scripts/Admin', 'scripts/Manga', 'vendor', 'storage/admin-jobs'] as $directory)
    mkdir($fixture . '/' . $directory, 0700, true);
$run = static function (array $command) use ($fixture): int
{
    $process = proc_open($command, [0 => ['pipe', 'r'], 1 => ['file', $fixture . '/launcher.log', 'a'], 2 => ['file', $fixture . '/launcher.log', 'a']], $pipes, $fixture);
    if (!is_resource($process)) throw new RuntimeException('Cannot start launcher fixture.');
    fclose($pipes[0]);
    return proc_close($process);
};
try
{
    copy($source . '/App/Services/Admin/AdminJob.php', $fixture . '/App/Services/Admin/AdminJob.php');
    copy($source . '/App/Services/Admin/ReleaseJob.php', $fixture . '/App/Services/Admin/ReleaseJob.php');
    copy($source . '/scripts/Admin/run-releases.php', $fixture . '/scripts/Admin/run-releases.php');
    file_put_contents($fixture . '/vendor/autoload.php', '<?php require dirname(__DIR__) . "/App/Services/Admin/AdminJob.php"; require dirname(__DIR__) . "/App/Services/Admin/ReleaseJob.php";');
    file_put_contents($fixture . '/scripts/Manga/sync-releases.php', '<?php echo "Fixture sync completed\n";');
    file_put_contents($fixture . '/launch.php', '<?php require __DIR__ . "/vendor/autoload.php"; function env($key, $default = null) { return $key === "ADMIN_COMMAND_PHP" ? PHP_BINARY : $default; } App\Services\Admin\ReleaseJob::start();');
    if ($run([PHP_BINARY, $fixture . '/launch.php']) !== 0) throw new RuntimeException('Launcher failed: ' . file_get_contents($fixture . '/launcher.log'));
    $statePath = $fixture . '/storage/admin-jobs/releases.json';
    $deadline = microtime(true) + 15;
    do {
        usleep(100000);
        $state = json_decode((string) file_get_contents($statePath), true);
    } while (($state['state'] ?? null) !== 'done' && microtime(true) < $deadline);
    if (($state['state'] ?? null) !== 'done') throw new RuntimeException('Detached worker did not complete.');
    if (!str_contains((string) file_get_contents($fixture . '/storage/admin-jobs/releases.log'), 'Fixture sync completed')) throw new RuntimeException('Worker output not captured.');
    file_put_contents($fixture . '/scripts/Manga/sync-releases.php', '<?php if (($argv[1] ?? null) !== "1" || count($argv) !== 2) exit(1); echo "Personal releases sync completed\n";');
    file_put_contents($fixture . '/launch-self.php', str_replace('::start();', '::start(1);', (string) file_get_contents($fixture . '/launch.php')));
    if ($run([PHP_BINARY, $fixture . '/launch-self.php']) !== 0) throw new RuntimeException('Personal launcher failed.');
    $deadline = microtime(true) + 15;
    do {
        usleep(100000);
        $state = json_decode((string) file_get_contents($statePath), true);
    } while (($state['state'] ?? null) !== 'done' && microtime(true) < $deadline);
    if (($state['state'] ?? null) !== 'done') throw new RuntimeException('Personal worker did not receive the account ID.');
    file_put_contents($statePath, json_encode(['state' => 'queued', 'updated' => time()], JSON_THROW_ON_ERROR));
    if ($run([PHP_BINARY, $fixture . '/launch.php']) === 0) throw new RuntimeException('Duplicate queued job accepted.');
    $state = json_decode((string) file_get_contents($statePath), true);
    if (($state['state'] ?? null) !== 'queued') throw new RuntimeException('Duplicate request damaged existing job state.');
    file_put_contents($fixture . '/scripts/Manga/sync-releases.php', '<?php echo "Fixture failure\n"; exit(1);');
    if ($run([PHP_BINARY, $fixture . '/scripts/Admin/run-releases.php']) !== 0) throw new RuntimeException('Failure worker fixture failed.');
    $state = json_decode((string) file_get_contents($statePath), true);
    if (($state['state'] ?? null) !== 'failed') throw new RuntimeException('Sync failure not reported.');
    echo "PASS: detached launcher, output capture, duplicate rejection and failed sync status.\n";
}
finally
{
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($fixture, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
    foreach ($files as $file)
    { if ($file->isDir()) rmdir($file->getPathname()); else unlink($file->getPathname()); }
    rmdir($fixture);
}
