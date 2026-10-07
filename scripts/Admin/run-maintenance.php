<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli')
{ http_response_code(404); exit; }
$root = dirname(__DIR__, 2);
require $root . '/vendor/autoload.php';
use App\Services\Admin\MaintenanceJob;
$task = $argv[1] ?? '';
$commands = match ($task)
{
    'doctor' => [[PHP_BINARY, $root . '/scripts/Tools/doctor.php']],
    'assets' => [[PHP_BINARY, $root . '/scripts/Assets/build-assets.php']],
    'js-prune' => [[PHP_BINARY, $root . '/scripts/Assets/JavaScript/prune-javascript.php']],
    'js-prune-force' => [[PHP_BINARY, $root . '/scripts/Assets/JavaScript/prune-javascript.php', '--force']],
    'images-check' => [[PHP_BINARY, $root . '/scripts/Assets/Images/check-images.php']],
    'images' => [[PHP_BINARY, $root . '/scripts/Assets/Images/build-profile-images.php'], [PHP_BINARY, $root . '/scripts/Assets/Images/optimize-thumbnails.php', '--apply']],
    'cache' => [[PHP_BINARY, $root . '/scripts/Maintenance/clear-runtime.php', 'cache']],
    'reset' => [[PHP_BINARY, $root . '/scripts/Admin/reset-runtime.php']],
    'migrations-check' => [[PHP_BINARY, $root . '/scripts/Database/migrate.php', 'status']],
    'migrations-create' => [[PHP_BINARY, $root . '/scripts/Database/create-migration.php', 'create']],
    'migrations' => [[PHP_BINARY, $root . '/scripts/Database/migrate.php', 'apply']],
    'backup' => [[PHP_BINARY, $root . '/scripts/Database/backup-database.php']],
    default => throw new InvalidArgumentException('Unknown maintenance command.')
};
$lock = fopen(MaintenanceJob::directory() . '/maintenance.lock', 'c');
if ($lock === false || !flock($lock, LOCK_EX)) exit(1);
try
{
    if (MaintenanceJob::status()['state'] !== 'queued') exit(1);
    MaintenanceJob::writeState('running');
    $log = MaintenanceJob::directory() . '/maintenance.log';
    $success = true;
    foreach ($commands as $command)
    {
        $process = proc_open($command, [0 => ['pipe', 'r'], 1 => ['file', $log, 'a'], 2 => ['file', $log, 'a']], $pipes, $root);
        if (!is_resource($process)) throw new RuntimeException('Cannot start maintenance.');
        fclose($pipes[0]);
        if (proc_close($process) !== 0)
        { $success = false; break; }
    }
    MaintenanceJob::writeState($success ? 'done' : 'failed');
} catch (Throwable $exception)
{
    file_put_contents(MaintenanceJob::directory() . '/maintenance.log', $exception->getMessage() . PHP_EOL, FILE_APPEND);
    MaintenanceJob::writeState('failed');
} finally
{ flock($lock, LOCK_UN); fclose($lock); }
