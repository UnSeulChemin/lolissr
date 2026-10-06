<?php

declare(strict_types=1);

require dirname(__DIR__, 2) . '/Http/Support/HttpTestStatistics.php';
require dirname(__DIR__, 2) . '/Http/Support/HttpHtmlReport.php';

function sample(string $label, float $duration, string $method = 'GET', string $status = 'OK', int $httpStatus = 200): array
{
    return ['label' => $label, 'path' => '/same', 'method' => $method, 'status' => $status,
        'http_status' => $httpStatus, 'duration' => $duration];
}

$results = [sample('HTML', 0.001), sample('HTML', 0.009), sample('JSON', 0.008),
    sample('JSON', 0.001), sample('JSON', 0.003), sample('failed', 1.0, status: 'FAIL'),
    sample('mutation', 1.0, method: 'POST'), sample('not found', 1.0, httpStatus: 404)];
$rows = HttpTestStatistics::slowestReads($results);
if (count($rows) !== 2 || $rows[0]['label'] !== 'HTML' || abs($rows[0]['median'] - 0.005) > 0.0000001
    || $rows[0]['max'] !== 0.009 || $rows[0]['samples'] !== 2 || $rows[1]['median'] !== 0.003)
    throw new RuntimeException('Incorrect medians, grouping or read filtering.');
$many = [];
for ($i = 0; $i < 15; $i++) $many[] = sample('case ' . $i, $i / 1000);
$rows = HttpTestStatistics::slowestReads($many);
if (count($rows) !== 10 || $rows[0]['label'] !== 'case 14') throw new RuntimeException('Incorrect top 10.');
if (HttpTestStatistics::slowestReads([]) !== []) throw new RuntimeException('Empty ranking.');
$file = tempnam(sys_get_temp_dir(), 'http-profile-');
if ($file === false) throw new RuntimeException('Cannot create report fixture.');
try
{
    HttpHtmlReport::generate([sample('<script>unsafe</script>', 0.004)], new HttpTestStatistics(), $file);
    $html = (string) file_get_contents($file);
    if (!str_contains($html, 'Top 10 lectures HTTP') || !str_contains($html, '4.00 ms')
        || str_contains($html, '<script>unsafe</script>')) throw new RuntimeException('Unsafe/incomplete timing report.');
}
finally
{
    unlink($file);
}
echo "PASS: HTTP medians, maxima, representations, top 10, failed/mutation exclusion and escaped HTML report.\n";
