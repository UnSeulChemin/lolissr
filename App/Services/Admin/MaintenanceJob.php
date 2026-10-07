<?php
declare(strict_types=1);
namespace App\Services\Admin;
use RuntimeException;
final class MaintenanceJob
{
    public static function directory(): string
    { return dirname(__DIR__, 3) . '/storage/admin-jobs'; }
    /** @return array{state: string, updated: int} */
    public static function status(): array
    {
        $path = self::directory() . '/maintenance.json';
        $data = null;
        $handle = @fopen($path, 'rb');
        if ($handle !== false)
        {
            try
            {
                if (!flock($handle, LOCK_SH)) throw new RuntimeException('Impossible de lire l’état de la commande.');
                $contents = stream_get_contents($handle);
                $data = $contents === false ? null : json_decode($contents, true);
            }
            finally
            {
                flock($handle, LOCK_UN);
                fclose($handle);
            }
        }
        if (!is_array($data) || !is_string($data['state'] ?? null) || !is_int($data['updated'] ?? null)) return ['state' => 'idle', 'updated' => 0];
        if (in_array($data['state'], ['queued', 'running'], true) && time() - $data['updated'] > 7200) return ['state' => 'interrupted', 'updated' => $data['updated']];
        return ['state' => $data['state'], 'updated' => $data['updated']];
    }
    public static function output(): string
    {
        $path = self::directory() . '/maintenance.log';
        if (!is_file($path)) return '';
        $handle = fopen($path, 'rb');
        if ($handle === false) return '';
        try
        {
            $size = filesize($path);
            if ($size !== false && $size > 16000) fseek($handle, -16000, SEEK_END);
            $output = stream_get_contents($handle);
            return $output === false ? '' : $output;
        } finally
        { fclose($handle); }
    }
    public static function writeState(string $state): void
    {
        if (file_put_contents(self::directory() . '/maintenance.json', json_encode(['state' => $state, 'updated' => time()], JSON_THROW_ON_ERROR), LOCK_EX) === false) throw new RuntimeException('Impossible de sauvegarder l’état de la commande.');
    }
    public static function start(string $task, ?int $ownerId = null): void
    {
        if (!in_array($task, ['doctor', 'assets', 'js-prune', 'js-prune-force', 'images-check', 'images', 'cache', 'reset', 'backup', 'xp-check', 'xp-apply', 'migrations-create', 'migrations-check', 'migrations'], true)) throw new RuntimeException('Commande invalide.');
        if ($ownerId !== null && ($ownerId < 1 || !in_array($task, ['xp-check', 'xp-apply'], true))) throw new RuntimeException('Compte invalide.');
        $directory = self::directory();
        if (!is_dir($directory) && !mkdir($directory, 0700, true) && !is_dir($directory)) throw new RuntimeException('Stockage des commandes indisponible.');
        $lock = fopen($directory . '/maintenance.lock', 'c');
        if ($lock === false) throw new RuntimeException('Verrou des commandes indisponible.');
        $queued = false;
        try
        {
            if (!flock($lock, LOCK_EX | LOCK_NB)) throw new RuntimeException('Une actualisation est déjà en cours.');
            if (in_array(self::status()['state'], ['queued', 'running'], true)) throw new RuntimeException('Une actualisation est déjà en cours.');
            $php = trim((string) env('ADMIN_COMMAND_PHP', ''));
            if ($php === '') $php = PHP_BINDIR . (PHP_OS_FAMILY === 'Windows' ? '/php.exe' : '/php');
            if (!is_file($php)) throw new RuntimeException('Configurer ADMIN_COMMAND_PHP avec le chemin de PHP CLI.');
            $worker = dirname(__DIR__, 3) . '/scripts/Admin/run-maintenance.php';
            self::writeState('queued');
            $queued = true;
            if (file_put_contents($directory . '/maintenance.log', '') === false) throw new RuntimeException('Journal des commandes indisponible.');
            if (PHP_OS_FAMILY === 'Windows')
            {
                $quote = static fn (string $value): string => "'" . str_replace("'", "''", $value) . "'";
                $argument = '"' . $worker . '"' . (' ' . $task) . ($ownerId !== null ? ' ' . $ownerId : '');
                $command = ['powershell.exe', '-NoProfile', '-NonInteractive', '-WindowStyle', 'Hidden', '-Command', 'Start-Process -WindowStyle Hidden -FilePath ' . $quote($php) . ' -ArgumentList ' . $quote($argument) . ' -WorkingDirectory ' . $quote(dirname(__DIR__, 3)) . ' -ErrorAction Stop'];
            } else
            {
                $command = ['/bin/sh', '-c', 'nohup ' . escapeshellarg($php) . ' ' . escapeshellarg($worker) . (' ' . $task) . ($ownerId !== null ? ' ' . $ownerId : '') . ' >/dev/null 2>&1 </dev/null &'];
            }
            $process = proc_open($command, [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
            if (!is_resource($process)) throw new RuntimeException('Impossible de lancer le traitement en arrière-plan.');
            fclose($pipes[0]); stream_get_contents($pipes[1]); stream_get_contents($pipes[2]);
            fclose($pipes[1]); fclose($pipes[2]);
            if (proc_close($process) !== 0) throw new RuntimeException('Impossible de lancer le traitement en arrière-plan.');
        } catch (RuntimeException $exception)
        {
            if ($queued) self::writeState('failed');
            throw $exception;
        } finally
        { flock($lock, LOCK_UN); fclose($lock); }
    }
}
