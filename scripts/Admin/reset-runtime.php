<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$root = dirname(__DIR__, 2);
$composer = null;
foreach (explode(PATH_SEPARATOR, (string) getenv('PATH')) as $directory)
{
    $directory = trim($directory, '"');
    if ($directory === '') continue;
    if (is_file($directory . '/composer.phar')) { $composer = [PHP_BINARY, $directory . '/composer.phar']; break; }
    if (PHP_OS_FAMILY !== 'Windows' && is_file($directory . '/composer') && is_executable($directory . '/composer')) { $composer = [$directory . '/composer']; break; }
}
if ($composer === null) { fwrite(STDERR, "Composer introuvable dans le PATH du serveur.\n"); exit(1); }
$commands = [];
foreach (['logs', 'cache', 'sessions'] as $target) $commands[] = [PHP_BINARY, $root . '/scripts/Maintenance/clear-runtime.php', $target];
$commands[] = [...$composer, 'dump-autoload', '--no-interaction'];
foreach ($commands as $command)
{
    $process = proc_open($command, [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, $root);
    if (!is_resource($process)) exit(1);
    fclose($pipes[0]);
    stream_set_blocking($pipes[1], false);
    stream_set_blocking($pipes[2], false);
    while (!feof($pipes[1]) || !feof($pipes[2]))
    {
        $output = stream_get_contents($pipes[1]);
        $errors = stream_get_contents($pipes[2]);
        if ($output !== false && $output !== '') fwrite(STDOUT, $output);
        if ($errors !== false && $errors !== '') fwrite(STDERR, $errors);
        usleep(10000);
    }
    fclose($pipes[1]);
    fclose($pipes[2]);
    $code = proc_close($process);
    if ($code !== 0) exit($code > 0 ? $code : 1);
}
