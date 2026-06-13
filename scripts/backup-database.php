<?php

declare(strict_types=1);

use Framework\Application\Bootstrap;

define('ROOT', dirname(__DIR__));

require ROOT . '/vendor/autoload.php';
require ROOT . '/Framework/Support/helpers.php';

Bootstrap::loadEnvOnly();

$date =
    date('Y-m-d');

$host =
    (string) env('DB_HOST');

$port =
    (string) env('DB_PORT');

$database =
    (string) env('DB_NAME');

$user =
    (string) env('DB_USER');

$password =
    (string) env('DB_PASS');

$mysqldump =
    (string) env('MYSQLDUMP_PATH');

$backupDirectory =
    ROOT
    . '/storage/backups/database';

if (!is_dir($backupDirectory))
{
    mkdir(
        $backupDirectory,
        0755,
        true,
    );
}

$backupFile =
    $backupDirectory
    . '/backup-'
    . $date
    . '.sql';

$command =
    sprintf(
        '"%s" -h%s -P%s -u%s -p%s %s > "%s"',
        $mysqldump,
        escapeshellarg($host),
        escapeshellarg($port),
        escapeshellarg($user),
        escapeshellarg($password),
        escapeshellarg($database),
        $backupFile,
    );

passthru(
    $command,
    $result,
);

if ($result !== 0)
{
    echo PHP_EOL;
    echo 'Backup failed.';
    echo PHP_EOL;

    exit(1);
}

echo PHP_EOL;
echo 'Backup completed: ';
echo basename($backupFile);
echo PHP_EOL;