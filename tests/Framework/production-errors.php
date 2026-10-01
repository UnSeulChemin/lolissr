<?php

declare(strict_types=1);
require dirname(__DIR__, 2) . '/phpstan-bootstrap.php';

use Framework\Application\Bootstrap;
use Framework\Config\Env;
use Framework\Http\ErrorHandler;
use Framework\Support\Logger;

if (($argv[1] ?? '') === 'child')
{
    Env::set('APP_DEBUG', false);
    Env::set('LOG_ENABLED', true);
    (new ReflectionProperty(Logger::class, 'directory'))->setValue(null, $argv[2]);
    (new ReflectionMethod(Bootstrap::class, 'configureDebug'))->invoke(null);
    ErrorHandler::register();
    @trigger_error('suppressed fixture', E_USER_WARNING);
    trigger_error('private production warning fixture', E_USER_WARNING);
    exit(42);
}

$directory = sys_get_temp_dir() . '/production-errors-' . bin2hex(random_bytes(8));
mkdir($directory);
try
{
    $process = proc_open([PHP_BINARY, __FILE__, 'child', $directory], [
        0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w'],
    ], $pipes);
    if (! is_resource($process)) throw new RuntimeException('Cannot run production error fixture.');
    fclose($pipes[0]);
    $output = stream_get_contents($pipes[1]);
    $error = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    if (proc_close($process) !== 0 || $error !== '') throw new RuntimeException('Error fixture failed: ' . $error);
    if ($output !== 'Une erreur interne est survenue.') throw new RuntimeException('Private error exposed or response changed.');
    $logs = glob($directory . '/app-*.log') ?: [];
    if (count($logs) !== 1) throw new RuntimeException('Production warning not logged.');
    $content = file_get_contents($logs[0]);
    if (! str_contains($content, 'private production warning fixture') || str_contains($content, 'suppressed fixture'))
        throw new RuntimeException('Wrong production warning logging.');
}
finally
{
    foreach (glob($directory . '/*') ?: [] as $file) unlink($file);
    if (is_file($directory . '/.cleanup.lock')) unlink($directory . '/.cleanup.lock');
    rmdir($directory);
}
echo "PASS: production warnings are logged, private details hidden and suppressed errors respected.\n";
