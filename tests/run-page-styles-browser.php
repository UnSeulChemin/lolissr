<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli')
{
    http_response_code(404);
    exit;
}

$root = dirname(__DIR__);
$baseUrl = rtrim($argv[1] ?? 'http://localhost/lolissr', '/');
$browser = 'C:\\Program Files (x86)\\Microsoft\\Edge\\Application\\msedge.exe';
$id = bin2hex(random_bytes(12));
$fixtureName = '__css_check_' . $id . '.html';
$fixturePath = $root . '/public/' . $fixtureName;
$profile = sys_get_temp_dir() . '/lolissr-css-' . $id;
$output = tmpfile();
$errors = tmpfile();
$process = null;

try
{
    if (!is_file($browser) || $output === false || $errors === false)
    {
        throw new RuntimeException('Edge or temporary output files unavailable.');
    }

    $testCode = file_get_contents($argv[2] ?? __DIR__ . '/page-styles-browser.js');
    $fixture = <<<'HTML'
<!doctype html><html><head><link rel="stylesheet" href="css/app.css" data-test-common></head>
<body><pre id="result">RUNNING</pre><script type="module">
import {preparePageStyles} from "./js/router/page-styles.js";
HTML;
    if (isset($argv[3]))
    {
        $payload = json_decode((string) file_get_contents($argv[3]), true, 512, JSON_THROW_ON_ERROR);
        $fixture .= 'window.cssComparison = ' . json_encode($payload, JSON_HEX_TAG | JSON_THROW_ON_ERROR) . ';';
    }
    $fixture .= $testCode;
    $fixture .= <<<'HTML'
try {
    const results = await testPageStyles(preparePageStyles);
    document.getElementById('result').textContent = 'PASS ' + results.length + ' checks\n' + results.join('\n');
} catch (error) {
    document.getElementById('result').textContent = 'FAIL ' + error.stack;
}
</script></body></html>
HTML;
    file_put_contents($fixturePath, $fixture);

    $process = proc_open(
        [$browser, '--headless', '--disable-gpu', '--no-first-run', '--no-default-browser-check',
            '--user-data-dir=' . $profile, '--dump-dom', '--virtual-time-budget=15000', $baseUrl . '/' . $fixtureName],
        [0 => ['pipe', 'r'], 1 => $output, 2 => $errors],
        $pipes,
        $root,
        null,
        ['bypass_shell' => true]
    );

    if (!is_resource($process))
    {
        throw new RuntimeException('Unable to start Edge.');
    }

    fclose($pipes[0]);
    $deadline = microtime(true) + 45;

    while (proc_get_status($process)['running'])
    {
        if (microtime(true) > $deadline)
        {
            throw new RuntimeException('Browser test timed out.');
        }
        usleep(100000);
    }

    rewind($output);
    $html = stream_get_contents($output);

    if (!is_string($html) || preg_match('/<pre id="result">(PASS [\s\S]*?)<\/pre>/', $html, $matches) !== 1)
    {
        throw new RuntimeException('Browser checks failed: ' . (string) $html);
    }

    echo $matches[1] . PHP_EOL;
}
finally
{
    if (is_resource($process))
    {
        if (proc_get_status($process)['running']) proc_terminate($process);
        proc_close($process);
    }
    if (is_resource($output)) fclose($output);
    if (is_resource($errors)) fclose($errors);
    if (is_file($fixturePath)) unlink($fixturePath);

    // Only remove the exact temporary profile allocated by this run.
    $resolvedProfile = realpath($profile);
    $resolvedTemp = realpath(sys_get_temp_dir());
    if ($resolvedProfile !== false && $resolvedTemp !== false
        && dirname($resolvedProfile) === $resolvedTemp
        && basename($resolvedProfile) === 'lolissr-css-' . $id)
    {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($resolvedProfile, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($files as $file)
        {
            if ($file->isDir() && !$file->isLink()) @rmdir($file->getPathname());
            else @unlink($file->getPathname());
        }
        @rmdir($resolvedProfile);
    }
}
