<?php

declare(strict_types=1);

use Framework\Application\Bootstrap;

const MAX_BACKUPS = 20;
const MIN_BACKUP_SIZE = 100;

define('ROOT', dirname(__DIR__));

require ROOT . '/vendor/autoload.php';
require ROOT . '/Framework/Support/Helpers.php';

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

    fail("Missing required environment variable: {$key}");
}

if (! is_file($mysqldumpPath))
{
    fail('mysqldump executable not found: ' . $mysqldumpPath);
}

$backupDirectory = ROOT . '/storage/backups/database';

if (! is_dir($backupDirectory) && ! mkdir($backupDirectory, 0755, true) && ! is_dir($backupDirectory))
{
    fail('Unable to create backup directory.');
}

$timestamp = date('Y-m-d_H-i-s');
$backupFile = $backupDirectory . DIRECTORY_SEPARATOR . 'backup-' . $timestamp . '.sql';

$temporaryConfigFile = tempnam(sys_get_temp_dir(), 'lolissr-mysql-');

if ($temporaryConfigFile === false)
{
    fail('Unable to create temporary MySQL configuration.');
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
        fail('Unable to write temporary MySQL configuration.');
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
        removeFile($backupFile);

        fail('Database backup failed.');
    }

    validateBackupFile($backupFile);
    cleanOldBackups($backupDirectory);

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

function validateBackupFile(string $backupFile): void
{
    clearstatcache(true, $backupFile);

    if (! is_file($backupFile))
    {
        fail('Backup file was not created.');
    }

    $backupSize = filesize($backupFile);

    if ($backupSize === false || $backupSize < MIN_BACKUP_SIZE)
    {
        removeFile($backupFile);

        fail(
            'Backup file is missing or too small. Minimum expected size: '
            . MIN_BACKUP_SIZE
            . ' bytes.'
        );
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

function cleanOldBackups(string $backupDirectory): void
{
    $files = glob(
        $backupDirectory
        . DIRECTORY_SEPARATOR
        . 'backup-*.sql'
    ) ?: [];

    usort(
        $files,
        static function (string $left, string $right): int
        {
            $leftModifiedAt = filemtime($left);
            $rightModifiedAt = filemtime($right);

            return ($rightModifiedAt ?: 0) <=> ($leftModifiedAt ?: 0);
        }
    );

    foreach (array_slice($files, MAX_BACKUPS) as $file)
    {
        if (! is_file($file))
        {
            continue;
        }

        if (! unlink($file))
        {
            echo PHP_EOL;
            echo '[WARNING] Unable to remove old backup: ' . basename($file);
            echo PHP_EOL;
        }
    }
}

function removeFile(string $path): void
{
    if (is_file($path) && ! unlink($path))
    {
        echo PHP_EOL;
        echo '[WARNING] Unable to remove file: ' . basename($path);
        echo PHP_EOL;
    }
}

function fail(string $message): never
{
    echo PHP_EOL;
    echo '[FAILED] ' . $message;
    echo PHP_EOL;

    exit(1);
}