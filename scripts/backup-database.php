<?php

declare(strict_types=1);

use Framework\Application\Bootstrap;

define('ROOT', dirname(__DIR__));

require ROOT . '/vendor/autoload.php';
require ROOT . '/Framework/Support/helpers.php';

Bootstrap::loadEnvOnly();

$timestamp = date('Y-m-d_H-i-s');
$backupDirectory = ROOT . '/storage/backups/database';

if (! is_dir($backupDirectory) && ! mkdir($backupDirectory, 0755, true) && ! is_dir($backupDirectory))
{
    echo PHP_EOL;
    echo '[FAILED] Unable to create backup directory.';
    echo PHP_EOL;

    exit(1);
}

$backupFile = $backupDirectory . '/backup-' . $timestamp . '.sql';

$mysqldumpPath = (string) env('MYSQLDUMP_PATH');
$host = (string) env('DB_HOST');
$port = (string) env('DB_PORT');
$user = (string) env('DB_USER');
$password = (string) env('DB_PASS');
$database = (string) env('DB_NAME');

$command = sprintf(
    '%s --single-transaction --routines --triggers --events --hex-blob --default-character-set=utf8mb4 --host=%s --port=%s --user=%s --password=%s --result-file=%s %s',
    escapeshellarg($mysqldumpPath),
    escapeshellarg($host),
    escapeshellarg($port),
    escapeshellarg($user),
    escapeshellarg($password),
    escapeshellarg($backupFile),
    escapeshellarg($database),
);

passthru($command, $result);

if ($result !== 0)
{
    if (is_file($backupFile))
    {
        unlink($backupFile);
    }

    echo PHP_EOL;
    echo '[FAILED] Database backup failed.';
    echo PHP_EOL;

    exit(1);
}

if (! is_file($backupFile) || filesize($backupFile) === 0)
{
    if (is_file($backupFile))
    {
        unlink($backupFile);
    }

    echo PHP_EOL;
    echo '[FAILED] Backup file is empty.';
    echo PHP_EOL;

    exit(1);
}

echo PHP_EOL;
echo '[OK] Backup created: ' . basename($backupFile);
echo PHP_EOL;