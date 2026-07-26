<?php

declare(strict_types=1);

use Framework\Application\Bootstrap;

define('ROOT', dirname(__DIR__));

require ROOT . '/vendor/autoload.php';
require ROOT . '/Framework/Support/helpers.php';

Bootstrap::loadEnvOnly();

$mysqldumpPath = trim((string) env('MYSQLDUMP_PATH'));
$host = trim((string) env('DB_HOST'));
$port = trim((string) env('DB_PORT'));
$user = trim((string) env('DB_USER'));
$password = (string) env('DB_PASS');
$database = trim((string) env('DB_NAME'));

$requiredConfiguration = [
    'MYSQLDUMP_PATH' => $mysqldumpPath,
    'DB_HOST' => $host,
    'DB_PORT' => $port,
    'DB_USER' => $user,
    'DB_NAME' => $database,
];

foreach ($requiredConfiguration as $key => $value)
{
    if ($value !== '')
    {
        continue;
    }

    echo PHP_EOL;
    echo "[FAILED] Missing required environment variable: {$key}";
    echo PHP_EOL;

    exit(1);
}

if (! is_file($mysqldumpPath))
{
    echo PHP_EOL;
    echo '[FAILED] mysqldump executable not found: ' . $mysqldumpPath;
    echo PHP_EOL;

    exit(1);
}

$backupDirectory = ROOT . '/storage/backups/database';

if (! is_dir($backupDirectory) && ! mkdir($backupDirectory, 0755, true) && ! is_dir($backupDirectory))
{
    echo PHP_EOL;
    echo '[FAILED] Unable to create backup directory.';
    echo PHP_EOL;

    exit(1);
}

$timestamp = date('Y-m-d_H-i-s');
$backupFile = $backupDirectory . '/backup-' . $timestamp . '.sql';

$temporaryConfigFile = tempnam(sys_get_temp_dir(), 'lolissr-mysql-');

if ($temporaryConfigFile === false)
{
    echo PHP_EOL;
    echo '[FAILED] Unable to create temporary MySQL configuration.';
    echo PHP_EOL;

    exit(1);
}

try
{
    $configuration = implode(PHP_EOL, [
        '[client]',
        'host="' . escapeMysqlOptionValue($host) . '"',
        'port="' . escapeMysqlOptionValue($port) . '"',
        'user="' . escapeMysqlOptionValue($user) . '"',
        'password="' . escapeMysqlOptionValue($password) . '"',
        'default-character-set="utf8mb4"',
        '',
    ]);

    if (file_put_contents($temporaryConfigFile, $configuration, LOCK_EX) === false)
    {
        echo PHP_EOL;
        echo '[FAILED] Unable to write temporary MySQL configuration.';
        echo PHP_EOL;

        exit(1);
    }

    $command = sprintf(
        '%s --defaults-extra-file=%s --single-transaction --routines --triggers --events --hex-blob --result-file=%s %s',
        escapeshellarg($mysqldumpPath),
        escapeshellarg($temporaryConfigFile),
        escapeshellarg($backupFile),
        escapeshellarg($database)
    );

    passthru($command, $result);

    if ($result !== 0)
    {
        removeBackupFile($backupFile);

        echo PHP_EOL;
        echo '[FAILED] Database backup failed.';
        echo PHP_EOL;

        exit(1);
    }

    clearstatcache(true, $backupFile);

    $backupSize = is_file($backupFile)
        ? filesize($backupFile)
        : false;

    if ($backupSize === false || $backupSize <= 0)
    {
        removeBackupFile($backupFile);

        echo PHP_EOL;
        echo '[FAILED] Backup file is empty.';
        echo PHP_EOL;

        exit(1);
    }

    echo PHP_EOL;
    echo '[OK] Backup created: ' . basename($backupFile);
    echo PHP_EOL;
}
finally
{
    if (is_file($temporaryConfigFile) && ! unlink($temporaryConfigFile))
    {
        echo PHP_EOL;
        echo '[WARNING] Unable to remove temporary MySQL configuration.';
        echo PHP_EOL;
    }
}

function escapeMysqlOptionValue(string $value): string
{
    return str_replace(
        ['\\', '"', "\r", "\n"],
        ['\\\\', '\\"', '\r', '\n'],
        $value
    );
}

function removeBackupFile(string $backupFile): void
{
    if (is_file($backupFile))
    {
        unlink($backupFile);
    }
}