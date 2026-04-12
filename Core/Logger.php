<?php

namespace App\Core;

class Logger
{
    /**
     * écrit un message dans le fichier de log
     */
    private static function write(string $level, string $message): void
    {
        $logFile = ROOT . '/logs/app.log';
        $date = date('Y-m-d H:i:s');

        $formatted = "[{$date}] {$level}: {$message}" . PHP_EOL;

        file_put_contents($logFile, $formatted, FILE_APPEND);
    }

    /**
     * log erreur
     */
    public static function error(string $message): void
    {
        self::write('ERROR', $message);
    }

    /**
     * log warning
     */
    public static function warning(string $message): void
    {
        self::write('WARNING', $message);
    }

    /**
     * log info
     */
    public static function info(string $message): void
    {
        self::write('INFO', $message);
    }
}