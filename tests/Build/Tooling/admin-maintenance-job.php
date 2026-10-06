<?php
declare(strict_types=1);
$root = dirname(__DIR__, 3);
$fixture = sys_get_temp_dir() . '/maintenance-job-' . bin2hex(random_bytes(8));
foreach (['App/Services/Admin', 'scripts/Admin', 'scripts/Assets/Images', 'scripts/Maintenance', 'scripts/Tools', 'vendor', 'storage/admin-jobs'] as $directory) mkdir($fixture . '/' . $directory, 0700, true);
$run = static function (string $task) use ($fixture): void
{
    $process = proc_open([PHP_BINARY, $fixture . '/launch.php', $task], [0 => ['pipe', 'r'], 1 => ['file', $fixture . '/launcher.log', 'a'], 2 => ['file', $fixture . '/launcher.log', 'a']], $pipes, $fixture);
    if (!is_resource($process)) throw new RuntimeException('Cannot launch maintenance fixture.');
    fclose($pipes[0]);
    if (proc_close($process) !== 0) throw new RuntimeException('Maintenance launcher failed.');
};
$wait = static function (string $expected) use ($fixture): void
{
    $deadline = microtime(true) + 15;
    do {
        usleep(100000);
        $state = json_decode((string) file_get_contents($fixture . '/storage/admin-jobs/maintenance.json'), true);
    } while (($state['state'] ?? null) !== $expected && microtime(true) < $deadline);
    if (($state['state'] ?? null) !== $expected) throw new RuntimeException('Unexpected maintenance state.');
};
try
{
    copy($root . '/App/Services/Admin/MaintenanceJob.php', $fixture . '/App/Services/Admin/MaintenanceJob.php');
    copy($root . '/scripts/Admin/run-maintenance.php', $fixture . '/scripts/Admin/run-maintenance.php');
    file_put_contents($fixture . '/vendor/autoload.php', '<?php require dirname(__DIR__) . "/App/Services/Admin/MaintenanceJob.php";');
    file_put_contents($fixture . '/launch.php', '<?php require __DIR__ . "/vendor/autoload.php"; function env($key, $default = null) { return $key === "ADMIN_COMMAND_PHP" ? PHP_BINARY : $default; } App\Services\Admin\MaintenanceJob::start($argv[1]);');
    file_put_contents($fixture . '/scripts/Assets/Images/build-profile-images.php', '<?php echo "Profiles completed\n";');
    file_put_contents($fixture . '/scripts/Assets/Images/optimize-thumbnails.php', '<?php if (($argv[1] ?? null) !== "--apply") exit(1); echo "Thumbnails completed\n";');
    file_put_contents($fixture . '/scripts/Maintenance/clear-runtime.php', '<?php if (($argv[1] ?? null) !== "cache") exit(1); echo "Cache cleared\n";');
    $run('images'); $wait('done');
    $output = (string) file_get_contents($fixture . '/storage/admin-jobs/maintenance.log');
    if (!str_contains($output, "Profiles completed\nThumbnails completed")) throw new RuntimeException('Image build stages missing or out of order.');
    $run('cache'); $wait('done');
    if (!str_contains((string) file_get_contents($fixture . '/storage/admin-jobs/maintenance.log'), 'Cache cleared')) throw new RuntimeException('Cache command not executed.');
    file_put_contents($fixture . '/scripts/Tools/doctor.php', '<?php echo "Diagnostic completed\n";');
    $run('doctor'); $wait('done');
    if (!str_contains((string) file_get_contents($fixture . '/storage/admin-jobs/maintenance.log'), 'Diagnostic completed')) throw new RuntimeException('Doctor command not executed.');
    file_put_contents($fixture . '/scripts/Assets/build-assets.php', '<?php echo "Assets built\n";');
    $run('assets'); $wait('done');
    if (!str_contains((string) file_get_contents($fixture . '/storage/admin-jobs/maintenance.log'), 'Assets built')) throw new RuntimeException('Asset build command not executed.');
    file_put_contents($fixture . '/scripts/Assets/Images/check-images.php', '<?php if (count($argv) !== 1) exit(1); echo "Images checked\n";');
    $run('images-check'); $wait('done');
    if (!str_contains((string) file_get_contents($fixture . '/storage/admin-jobs/maintenance.log'), 'Images checked')) throw new RuntimeException('Image check command not executed.');
    file_put_contents($fixture . '/scripts/Assets/Images/build-profile-images.php', '<?php echo "Profile failure\n"; exit(1);');
    copy($root . '/scripts/Admin/reset-runtime.php', $fixture . '/scripts/Admin/reset-runtime.php');
    file_put_contents($fixture . '/composer.phar', '<?php if (($argv[1] ?? null) !== "dump-autoload") exit(1); echo "Autoload rebuilt\n";');
    file_put_contents($fixture . '/scripts/Maintenance/clear-runtime.php', '<?php if (!in_array($argv[1] ?? null, ["logs", "cache", "sessions"], true)) exit(1); echo $argv[1] . " cleared\n";');
    $previousPath = (string) getenv('PATH');
    putenv('PATH=' . $fixture . PATH_SEPARATOR . $previousPath);
    try { $run('reset'); $wait('done'); }
    finally { putenv('PATH=' . $previousPath); }
    $resetOutput = (string) file_get_contents($fixture . '/storage/admin-jobs/maintenance.log');
    if (!str_contains(str_replace("\r", '', $resetOutput), "logs cleared\ncache cleared\nsessions cleared\nAutoload rebuilt")) throw new RuntimeException('Global reset stages missing or out of order: ' . $resetOutput);
    $run('images'); $wait('failed');
    if (str_contains((string) file_get_contents($fixture . '/storage/admin-jobs/maintenance.log'), 'Thumbnails completed')) throw new RuntimeException('Image build continued after failure.');
    echo "PASS: isolated background image build, cache clear, output capture and stop on failure.\n";
} finally
{
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($fixture, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST) as $file)
    {
        if ($file->isDir()) rmdir($file->getPathname()); else unlink($file->getPathname());
    }
    rmdir($fixture);
}
