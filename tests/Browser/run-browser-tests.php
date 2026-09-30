<?php
declare(strict_types=1);

$base = $argv[1] ?? 'http://localhost/lolissr';
$commands = [[PHP_BINARY, __DIR__ . '/run-javascript-browser.php', $base]];
foreach (['page-styles-browser.js', 'spa-browser.js', 'route-initializers-browser.js', 'spa-lifecycle-browser.js', 'scroll-history-browser.js', 'flash-feedback-browser.js', 'navigation-cancel-browser.js'] as $test)
{
    $commands[] = [PHP_BINARY, __DIR__ . '/run-page-styles-browser.php', $base, __DIR__ . '/' . $test];
}
foreach ($commands as $command)
{
    $process = proc_open($command, [0 => ['pipe', 'r'], 1 => STDOUT, 2 => STDERR], $pipes, dirname(__DIR__, 2), null, ['bypass_shell' => true]);
    if (!is_resource($process)) throw new RuntimeException('Cannot start browser tests.');
    fclose($pipes[0]);
    $status = proc_close($process);
    if ($status !== 0) exit($status);
}
echo "PASS: all browser suites.\n";
