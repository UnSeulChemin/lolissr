<?php

declare(strict_types=1);

namespace App\Core\Support;

use App\Core\Config\Env;

class Logger
{
    /**
     * Retourne le dossier des logs.
     */
    private static function logDirectory(): string
    {
        return (string) Env::get('LOG_DIR', app_path('Storage/logs'));
    }

    /**
     * Retourne le fichier de log principal.
     */
    private static function logFile(): string
    {
        return self::logDirectory() . '/app.log';
    }

    /**
     * Écrit un message dans le fichier de log.
     */
    private static function write(string $level, string $message): void
    {
        $logDir = self::logDirectory();
        $logFile = self::logFile();

        if (!is_dir($logDir))
        {
            $created = mkdir($logDir, 0755, true);

            if (!$created && !is_dir($logDir))
            {
                return;
            }
        }

        $date = date('Y-m-d H:i:s');
        $formatted = "[{$date}] [{$level}] {$message}" . PHP_EOL;

        $result = @file_put_contents(
            $logFile,
            $formatted,
            FILE_APPEND | LOCK_EX
        );

        if ($result === false)
        {
            return;
        }
    }

    /**
     * Log une erreur.
     */
    public static function error(string $message): void
    {
        self::write('ERROR', $message);
    }

    /**
     * Log un warning.
     */
    public static function warning(string $message): void
    {
        self::write('WARNING', $message);
    }

    /**
     * Log une information.
     */
    public static function info(string $message): void
    {
        self::write('INFO', $message);
    }
}