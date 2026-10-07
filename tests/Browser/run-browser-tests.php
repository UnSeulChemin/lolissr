<?php
declare(strict_types=1);

$base = $argv[1] ?? 'http://localhost/lolissr';
$commands = [[PHP_BINARY, __DIR__ . '/run-bundle-browser.php', $base]];
$commands[] = [PHP_BINARY, __DIR__ . '/run-browser-scenario.php', $base, __DIR__ . '/Feedback/modal-text-browser.js'];
$commands[] = [PHP_BINARY, __DIR__ . '/run-browser-scenario.php', $base, __DIR__ . '/Feedback/mutation-lifecycle-browser.js'];
$commands[] = [PHP_BINARY, __DIR__ . '/run-browser-scenario.php', $base, __DIR__ . '/Feedback/create-form-browser.js'];
$commands[] = [PHP_BINARY, __DIR__ . '/run-browser-scenario.php', $base, __DIR__ . '/Manga/create-restoration-browser.js'];
$commands[] = [PHP_BINARY, __DIR__ . '/run-browser-scenario.php', $base, __DIR__ . '/Feedback/http-timeout-browser.js'];
$commands[] = [PHP_BINARY, __DIR__ . '/run-browser-scenario.php', $base, __DIR__ . '/Assets/collection-responsive-browser.js'];
$commands[] = [PHP_BINARY, __DIR__ . '/run-browser-scenario.php', $base, __DIR__ . '/Admin/job-status-browser.js'];
$commands[] = [PHP_BINARY, __DIR__ . '/run-browser-scenario.php', $base, __DIR__ . '/Manga/acquire-release-browser.js'];
$commands[] = [PHP_BINARY, __DIR__ . '/run-browser-scenario.php', $base, __DIR__ . '/Manga/hide-recommendation-browser.js'];
$commands[] = [PHP_BINARY, __DIR__ . '/run-browser-scenario.php', $base, __DIR__ . '/Manga/recommendation-pagination-browser.js'];
$commands[] = [PHP_BINARY, __DIR__ . '/run-browser-scenario.php', $base, __DIR__ . '/Search/search-browser.js'];
$commands[] = [PHP_BINARY, __DIR__ . '/run-browser-scenario.php', $base, __DIR__ . '/Navigation/prefetch-browser.js'];
$commands[] = [PHP_BINARY, __DIR__ . '/run-browser-scenario.php', $base, __DIR__ . '/Profile/profile-performance-browser.js'];
foreach (['Assets/page-styles-browser.js', 'Navigation/spa-browser.js', 'Navigation/route-initializers-browser.js', 'Navigation/spa-lifecycle-browser.js', 'Navigation/scroll-history-browser.js', 'Feedback/flash-feedback-browser.js', 'Chinois/flashcard-pages-browser.js', 'Navigation/navigation-cancel-browser.js', 'Navigation/header-click-browser.js'] as $test)
{
    $commands[] = [PHP_BINARY, __DIR__ . '/run-browser-scenario.php', $base, __DIR__ . '/' . $test];
}
foreach ($commands as $command)
{
    echo 'Browser scenario: ' . basename($command[3] ?? $command[1]) . PHP_EOL;
    $output = tmpfile();
    $errors = tmpfile();
    if ($output === false || $errors === false)
    {
        if (is_resource($output)) fclose($output);
        if (is_resource($errors)) fclose($errors);
        throw new RuntimeException('Cannot capture browser scenario output.');
    }
    try
    {
        // Forward from the parent: inherited redirected handles can overwrite output on Windows.
        $process = proc_open($command, [0 => ['pipe', 'r'], 1 => $output, 2 => $errors], $pipes, dirname(__DIR__, 2), null, ['bypass_shell' => true]);
        if (!is_resource($process)) throw new RuntimeException('Cannot start browser tests.');
        fclose($pipes[0]);
        $status = proc_close($process);
        rewind($output);
        rewind($errors);
        fpassthru($output);
        fwrite(STDERR, (string) stream_get_contents($errors));
    }
    finally
    {
        fclose($output);
        fclose($errors);
    }
    if ($status !== 0)
    {
        fwrite(STDERR, 'Browser scenario process failed with exit code ' . $status . PHP_EOL);
        // Windows crash codes may become zero when passed directly to exit().
        exit(1);
    }
}
echo "PASS: all browser suites.\n";
