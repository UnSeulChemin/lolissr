<?php

declare(strict_types=1);

$bootstrap =
    require __DIR__
    . '/Http/bootstrap-runner.php';

$base =
    $bootstrap['base'];

$tests =
    $bootstrap['tests'];

$stats =
    new Stats();

$results = [];

$globalStart =
    microtime(true);

echo PHP_EOL;
echo "======================================" . PHP_EOL;
echo "         LOLISSR HTTP TESTS" . PHP_EOL;
echo "======================================" . PHP_EOL;
echo PHP_EOL;

foreach ($tests as $test)
{
    $label =
        $test['label'];

    $path =
        $test['path'];

    $url =
        rtrim($base, '/')
        . '/'
        . ltrim($path, '/');

    $start =
        microtime(true);

    $response =
        http_get($url);

    $duration =
        microtime(true) - $start;

    if ($response['status'] === 200)
    {
        $stats->success($duration);

        echo "✅ {$label}" . PHP_EOL;

        $results[] = [
            'status' => 'OK',
            'label' => $label,
            'path' => $path,
            'duration' => $duration,
        ];

        continue;
    }

    $stats->fail($duration);

    echo
        "❌ {$label}"
        . " ({$response['status']})"
        . PHP_EOL;

    $results[] = [
        'status' => 'FAIL',
        'label' => $label,
        'path' => $path,
        'duration' => $duration,
    ];
}

$totalDuration =
    microtime(true)
    - $globalStart;

echo PHP_EOL;
echo "======================================" . PHP_EOL;
echo "Tests : " . $stats->total() . PHP_EOL;
echo "OK    : " . $stats->successCount() . PHP_EOL;
echo "FAIL  : " . $stats->failCount() . PHP_EOL;
echo "Temps : " . round($totalDuration, 3) . "s" . PHP_EOL;
echo "======================================" . PHP_EOL;

if (!is_dir(__DIR__ . '/Http/reports'))
{
    mkdir(
        __DIR__ . '/Http/reports',
        0777,
        true
    );
}

HtmlReport::generate(
    $results,
    $stats,
    __DIR__ . '/Http/reports/latest.html'
);

echo PHP_EOL;
echo "📄 Rapport HTML généré" . PHP_EOL;