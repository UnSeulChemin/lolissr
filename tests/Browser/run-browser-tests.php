<?php
declare(strict_types=1);

$base = $argv[1] ?? 'http://localhost/lolissr';
$commands = [[PHP_BINARY, __DIR__ . '/run-bundle-browser.php', $base]];
$commands[] = [PHP_BINARY, __DIR__ . '/run-browser-scenario.php', $base, __DIR__ . '/acquire-release-browser.js'];
$commands[] = [PHP_BINARY, __DIR__ . '/run-browser-scenario.php', $base, __DIR__ . '/hide-recommendation-browser.js'];
$commands[] = [PHP_BINARY, __DIR__ . '/run-browser-scenario.php', $base, __DIR__ . '/recommendation-pagination-browser.js'];
$commands[] = [PHP_BINARY, __DIR__ . '/run-browser-scenario.php', $base, __DIR__ . '/search-browser.js'];
$commands[] = [PHP_BINARY, __DIR__ . '/run-browser-scenario.php', $base, __DIR__ . '/prefetch-browser.js'];
$commands[] = [PHP_BINARY, __DIR__ . '/run-browser-scenario.php', $base, __DIR__ . '/profile-performance-browser.js'];
foreach (['page-styles-browser.js', 'spa-browser.js', 'route-initializers-browser.js', 'spa-lifecycle-browser.js', 'scroll-history-browser.js', 'flash-feedback-browser.js', 'flashcard-pages-browser.js', 'navigation-cancel-browser.js', 'header-click-browser.js'] as $test)
{
    $commands[] = [PHP_BINARY, __DIR__ . '/run-browser-scenario.php', $base, __DIR__ . '/' . $test];
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
