<?php

declare(strict_types=1);

$bootstrap =
    require __DIR__
    . '/bootstrap-runner.php';

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

/**
 * @param array<int,array<string,mixed>> $results
 */
function addResult(
    array &$results,
    string $status,
    string $category,
    string $label,
    string $path,
    int $httpStatus,
    int $expectedStatus,
    float $duration,
    string $reason,
    array $headers,
    string $body,
): void {

    $results[] = [

        'status' =>
            $status,

        'category' =>
            $category,

        'label' =>
            $label,

        'path' =>
            $path,

        'http_status' =>
            $httpStatus,

        'expected_status' =>
            $expectedStatus,

        'duration' =>
            $duration,

        'reason' =>
            $reason,

        'headers' =>
            implode(
                "\n",
                $headers,
            ),

        'body' =>
            substr(
                $body,
                0,
                3000,
            ),
    ];
}

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

    $headers =
        (array) (
            $test['headers']
            ?? []
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
        http_get(
            $url,
            $headers,
        );

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

    $responseHeaders =
        (array) (
            $response['headers']
            ?? []
        );

    $success =
        $status === $expectedStatus;

    $failureReason = '';

    /*
    |--------------------------------------------------------------------------
    | BODY NOT EMPTY
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
    | CONTAINS
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
    | JSON
    |--------------------------------------------------------------------------
    */

    if (
        $success
        && ($test['json'] ?? false)
    ) {

        if (
            !assert_json(
                $body,
            )
        ) {

            $success = false;

            $failureReason =
                'Invalid JSON';
        }
    }

    /*
    |--------------------------------------------------------------------------
    | HTML
    |--------------------------------------------------------------------------
    */

    if (
        $success
        && !($test['json'] ?? false)
        && !($test['fragment'] ?? false)
    ) {

        if (!assert_html($body))
        {
            $success = false;
            $failureReason = 'Invalid HTML';
        }

        if (
            $success
            && !assert_title($body)
        ) {
            $success = false;
            $failureReason = 'Missing title tag';
        }
    }

    /*
    |--------------------------------------------------------------------------
    | HEADERS
    |--------------------------------------------------------------------------
    */

    if (
        $success
        && isset(
            $test['header_contains']
        )
    ) {

        foreach (
            $test['header_contains']
            as $header
        ) {

            if (
                !assert_header(
                    $responseHeaders,
                    $header,
                )
            ) {

                $success = false;

                $failureReason =
                    'Missing header: '
                    . $header;

                break;
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | SUCCESS
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

        addResult(
            results: $results,
            status: 'OK',
            category: $category,
            label: $label,
            path: $path,
            httpStatus: $status,
            expectedStatus: $expectedStatus,
            duration: $duration,
            reason: '',
            headers: $responseHeaders,
            body: $body,
        );

        continue;
    }

    /*
    |--------------------------------------------------------------------------
    | FAILURE
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

    addResult(
        results: $results,
        status: 'FAIL',
        category: $category,
        label: $label,
        path: $path,
        httpStatus: $status,
        expectedStatus: $expectedStatus,
        duration: $duration,
        reason: $failureReason,
        headers: $responseHeaders,
        body: $body,
    );
}

$totalDuration =
    microtime(true)
    - $globalStart;

echo PHP_EOL;
echo str_repeat('=', 50) . PHP_EOL;
echo 'Tests   : '
    . $stats->total()
    . PHP_EOL;

echo 'OK      : '
    . $stats->successCount()
    . PHP_EOL;

echo 'FAIL    : '
    . $stats->failCount()
    . PHP_EOL;

echo 'Success : '
    . $stats->successRate()
    . '%'
    . PHP_EOL;

echo 'Moyenne : '
    . round(
        $stats->averageDuration()
        * 1000,
        2,
    )
    . 'ms'
    . PHP_EOL;

echo 'Temps   : '
    . round(
        $totalDuration,
        3,
    )
    . 's'
    . PHP_EOL;

echo str_repeat('=', 50)
    . PHP_EOL;

$reportDirectory =
    __DIR__
    . '/reports';

if (!is_dir($reportDirectory))
{
    mkdir(
        $reportDirectory,
        0755,
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
echo '📄 Rapport HTML généré'
    . PHP_EOL;

echo $reportFile
    . PHP_EOL;