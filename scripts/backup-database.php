<?php

declare(strict_types=1);

use Framework\Application\Bootstrap;

define('ROOT', dirname(__DIR__));

require ROOT . '/vendor/autoload.php';
require ROOT . '/Framework/Support/helpers.php';

Bootstrap::loadEnvOnly();

$timestamp =
    date('Y-m-d_H-i-s');

$backupDirectory =
    ROOT
    . '/storage/backups/database';

if (! is_dir($backupDirectory))
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
    . $timestamp
    . '.sql';

$command =
    sprintf(
        '"%s" -h%s -P%s -u%s -p%s %s > "%s"',
        env('MYSQLDUMP_PATH'),
        escapeshellarg((string) env('DB_HOST')),
        escapeshellarg((string) env('DB_PORT')),
        escapeshellarg((string) env('DB_USER')),
        escapeshellarg((string) env('DB_PASS')),
        escapeshellarg((string) env('DB_NAME')),
        $backupFile,
    );

passthru(
    $command,
    $result,
);

if ($result !== 0)
{
    echo PHP_EOL;
    echo '[FAILED] Database backup failed.';
    echo PHP_EOL;

    exit(1);
}

echo PHP_EOL;
echo '[OK] Backup created: ';
echo basename($backupFile);
echo PHP_EOL;