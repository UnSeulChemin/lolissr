<?php

namespace App\Core;

class Logger
{
    /**
     * Écrit un message dans le fichier de log.
     */
    private static function write(string $level, string $message): void
    {
        /**
         * Chemin du dossier logs.
         */
        $logDir = ROOT . '/logs';

        /**
         * Chemin du fichier log.
         */
        $logFile = $logDir . '/app.log';

        /**
         * Crée le dossier logs si inexistant.
         */
        if (!is_dir($logDir))
        {
            mkdir($logDir, 0755, true);
        }

        /**
         * Date du log.
         */
        $date = date('Y-m-d H:i:s');

        /**
         * Format du message.
         */
        $formatted = "[{$date}] {$level}: {$message}" . PHP_EOL;

        /**
         * Écriture sécurisée dans le fichier.
         */
        file_put_contents(
            $logFile,
            $formatted,
            FILE_APPEND | LOCK_EX
        );
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