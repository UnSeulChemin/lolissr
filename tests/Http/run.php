<?php

declare(strict_types=1);

$bootstrap =
    require __DIR__
    . '/bootstrap-runner.php';

$config =
    $bootstrap['config'];

$base =
    $bootstrap['base'];

$tests =
    $bootstrap['tests'];

$stats =
    new Stats();

$results = [];

$globalStart =
    microtime(true);

$currentCategory =
    null;

echo PHP_EOL;
echo str_repeat('=', 50) . PHP_EOL;
echo 'LOLISSR HTTP TESTS' . PHP_EOL;
echo str_repeat('=', 50) . PHP_EOL;
echo PHP_EOL;

foreach ($tests as $test)
{
    $category =
        (string) (
            $test['category']
            ?? 'General'
        );

    if ($category !== $currentCategory)
    {
        $currentCategory =
            $category;

        echo PHP_EOL;
        echo '--- '
            . $category
            . ' ---'
            . PHP_EOL;
    }

    $label =
        (string) (
            $test['label']
            ?? 'Sans label'
        );

    $path =
        (string) (
            $test['path']
            ?? '/'
        );

    $expectedStatus =
        (int) (
            $test['expected_status']
            ?? 200
        );

    $url =
        rtrim(
            $base,
            '/',
        )
        . '/'
        . ltrim(
            $path,
            '/',
        );

    $start =
        microtime(true);

    $response =
        http_get($url);

    $duration =
        microtime(true)
        - $start;

    $status =
        (int) (
            $response['status']
            ?? 0
        );

    $body =
        (string) (
            $response['body']
            ?? ''
        );

    $success =
        $status === $expectedStatus;

    $failureReason = '';

    /*
    |--------------------------------------------------------------------------
    | Empty Body
    |--------------------------------------------------------------------------
    */

    if (
        $success
        && !assert_not_empty_body(
            $body,
        )
    ) {

        $success = false;

        $failureReason =
            'Empty response body';
    }

    /*
    |--------------------------------------------------------------------------
    | Contains
    |--------------------------------------------------------------------------
    */

    if (
        $success
        && isset($test['contains'])
    ) {

        foreach (
            $test['contains']
            as $needle
        ) {

            if (
                !assert_contains(
                    $body,
                    $needle,
                )
            ) {

                $success = false;

                $failureReason =
                    'Missing text: '
                    . $needle;

                break;
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Success
    |--------------------------------------------------------------------------
    */

    if ($success)
    {
        $stats->success(
            $duration,
        );

        echo
            "✅ {$label}"
            . " [{$status}]"
            . PHP_EOL;

        $results[] = [
            'status' => 'OK',
            'category' => $category,
            'label' => $label,
            'path' => $path,
            'url' => $url,
            'http_status' => $status,
            'duration' => $duration,
            'reason' => '',
        ];

        continue;
    }

    /*
    |--------------------------------------------------------------------------
    | Failure
    |--------------------------------------------------------------------------
    */

    $stats->fail(
        $duration,
    );

    echo
        "❌ {$label}"
        . " [{$status}]";

    if ($failureReason !== '')
    {
        echo
            ' -> '
            . $failureReason;
    }

    echo PHP_EOL;

    $results[] = [
        'status' => 'FAIL',
        'category' => $category,
        'label' => $label,
        'path' => $path,
        'url' => $url,
        'http_status' => $status,
        'duration' => $duration,
        'reason' => $failureReason,
    ];
}

$totalDuration =
    microtime(true)
    - $globalStart;

echo PHP_EOL;
echo str_repeat('=', 50) . PHP_EOL;
echo 'Tests   : ' . $stats->total() . PHP_EOL;
echo 'OK      : ' . $stats->successCount() . PHP_EOL;
echo 'FAIL    : ' . $stats->failCount() . PHP_EOL;
echo 'Success : ' . $stats->successRate() . '%' . PHP_EOL;
echo 'Temps   : ' . round(
        $totalDuration,
        3,
    ) . 's'
    . PHP_EOL;
echo str_repeat('=', 50) . PHP_EOL;

$reportDirectory =
    __DIR__
    . '/reports';

if (!is_dir($reportDirectory))
{
    mkdir(
        $reportDirectory,
        0777,
        true,
    );
}

$reportFile =
    $reportDirectory
    . '/lolissr-http-report.html';

HtmlReport::generate(
    $results,
    $stats,
    $reportFile,
);

echo PHP_EOL;
echo '📄 Rapport HTML généré' . PHP_EOL;
echo $reportFile . PHP_EOL;