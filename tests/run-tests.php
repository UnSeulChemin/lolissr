<?php

declare(strict_types=1);

$bootstrap = require __DIR__ . '/Http/bootstrap-runner.php';

$base = (string) $bootstrap['base'];
$tests = (array) $bootstrap['tests'];

$stats = new Stats();

$results = [];

$globalStart = microtime(true);

echo PHP_EOL;
echo '========================================' . PHP_EOL;
echo '         LOLISSR HTTP TESTS' . PHP_EOL;
echo '========================================' . PHP_EOL;
echo PHP_EOL;

foreach ($tests as $test)
{
    $label = (string) ($test['label'] ?? 'Sans label');
    $path = (string) ($test['path'] ?? '/');

    $expectedStatus = (int) (
        $test['expected_status']
        ?? 200
    );

    $url =
        rtrim($base, '/')
        . '/'
        . ltrim($path, '/');

    $start = microtime(true);

    $response = http_get($url);

    $duration =
        microtime(true)
        - $start;

    $status =
        (int) ($response['status'] ?? 0);

    $success =
        $status === $expectedStatus;

    if ($success)
    {
        $stats->success($duration);

        echo
            "✅ {$label}"
            . " [{$status}]"
            . PHP_EOL;

        $results[] = [
            'status' => 'OK',
            'label' => $label,
            'path' => $path,
            'url' => $url,
            'http_status' => $status,
            'expected_status' => $expectedStatus,
            'duration' => $duration,
        ];

        continue;
    }

    $stats->fail($duration);

    echo
        "❌ {$label}"
        . " [{$status}]"
        . " attendu {$expectedStatus}"
        . PHP_EOL;

    $results[] = [
        'status' => 'FAIL',
        'label' => $label,
        'path' => $path,
        'url' => $url,
        'http_status' => $status,
        'expected_status' => $expectedStatus,
        'duration' => $duration,
    ];
}

$totalDuration =
    microtime(true)
    - $globalStart;

echo PHP_EOL;
echo '========================================' . PHP_EOL;
echo 'Tests : ' . $stats->total() . PHP_EOL;
echo 'OK    : ' . $stats->successCount() . PHP_EOL;
echo 'FAIL  : ' . $stats->failCount() . PHP_EOL;
echo 'Temps : ' . round($totalDuration, 3) . 's' . PHP_EOL;
echo '========================================' . PHP_EOL;

$reportDirectory =
    __DIR__
    . '/Http/reports';

if (!is_dir($reportDirectory))
{
    mkdir(
        $reportDirectory,
        0777,
        true,
    );
}

HtmlReport::generate(
    $results,
    $stats,
    $reportDirectory . '/latest.html',
);

echo PHP_EOL;
echo '📄 Rapport HTML généré' . PHP_EOL;
echo $reportDirectory . '/latest.html' . PHP_EOL;