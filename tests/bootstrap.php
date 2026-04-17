<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| CONFIG
|--------------------------------------------------------------------------
*/

$config = require __DIR__ . '/config.php';

$base = $config['base'];
$realSlug = $config['realSlug'];
$realNumero = $config['realNumero'];
$nonCanonicalSlug = $config['nonCanonicalSlug'];

$testCanonicalRedirect = $config['testCanonicalRedirect'];
$testPostAjouter = $config['testPostAjouter'];
$testPostUpdate = $config['testPostUpdate'];

$exportDirectory = $config['exportDirectory'];
$exportEnabled = $config['exportEnabled'];

/*
|--------------------------------------------------------------------------
| COULEURS TERMINAL
|--------------------------------------------------------------------------
*/

const C_RESET   = "\033[0m";
const C_BOLD    = "\033[1m";
const C_DIM     = "\033[2m";

const C_RED     = "\033[31m";
const C_GREEN   = "\033[32m";
const C_YELLOW  = "\033[33m";
const C_BLUE    = "\033[34m";
const C_MAGENTA = "\033[35m";
const C_CYAN    = "\033[36m";
const C_WHITE   = "\033[37m";

const C_BG_RED   = "\033[41m";
const C_BG_GREEN = "\033[42m";

/*
|--------------------------------------------------------------------------
| STORAGE RUNTIME
|--------------------------------------------------------------------------
*/

$plainOutput = '';
$allResults = [];

$tests = [];
$htmlChecks = [];
$postChecks = [];

$stats = [
    'global' => [
        'total' => 0,
        'success' => 0,
        'fail' => 0,
        'warn' => 0,
        'duration' => 0.0,
    ],
    'categories' => [],
];

$failures = [];
$warnings = [];

/*
|--------------------------------------------------------------------------
| HELPERS AFFICHAGE
|--------------------------------------------------------------------------
*/

function addPlain(string $text): void
{
    global $plainOutput;
    $plainOutput .= $text;
}

function out(string $text): void
{
    echo $text;
    addPlain(stripAnsi($text));
}

function outLine(string $text = ''): void
{
    out($text . PHP_EOL);
}

function stripAnsi(string $text): string
{
    return preg_replace('/\e\[[0-9;]*m/', '', $text) ?? $text;
}

function color(string $text, string $color): string
{
    return $color . $text . C_RESET;
}

function line(string $char = '─', int $length = 72): string
{
    return str_repeat($char, $length);
}

function sectionTitle(string $title): void
{
    outLine();
    outLine(color(line('═'), C_BLUE));
    outLine(color('  ' . $title, C_BOLD . C_CYAN));
    outLine(color(line('═'), C_BLUE));
    outLine();
}

function categoryTitle(string $title): void
{
    outLine();
    outLine(color('▶ ' . $title, C_BOLD . C_MAGENTA));
    outLine(color(line('─', 54), C_DIM));
}

function formatDuration(float $seconds): string
{
    return number_format($seconds * 1000, 2, ',', ' ') . ' ms';
}

function printOk(string $label, string $message = '', float $duration = 0.0): void
{
    $meta = [];

    if ($message !== '')
    {
        $meta[] = $message;
    }

    $meta[] = formatDuration($duration);

    outLine(
        color('✅ OK   ', C_GREEN . C_BOLD)
        . $label
        . ' '
        . color('(' . implode(' | ', $meta) . ')', C_DIM)
    );
}

function printFail(string $label, string $message = '', float $duration = 0.0): void
{
    $meta = [];

    if ($message !== '')
    {
        $meta[] = $message;
    }

    $meta[] = formatDuration($duration);

    outLine(
        color('❌ FAIL ', C_RED . C_BOLD)
        . $label
        . ' '
        . color('[' . implode(' | ', $meta) . ']', C_RED)
    );
}

function printWarn(string $label, string $message = '', float $duration = 0.0): void
{
    $meta = [];

    if ($message !== '')
    {
        $meta[] = $message;
    }

    $meta[] = formatDuration($duration);

    outLine(
        color('⚠ WARN ', C_YELLOW . C_BOLD)
        . $label
        . ' '
        . color('[' . implode(' | ', $meta) . ']', C_YELLOW)
    );
}

function recordResult(
    string $status,
    string $category,
    string $label,
    string $message,
    float $duration,
    ?string $url = null
): void
{
    global $allResults;

    $allResults[] = [
        'status' => $status,
        'category' => $category,
        'label' => $label,
        'message' => $message,
        'duration' => $duration,
        'url' => $url,
    ];
}

/*
|--------------------------------------------------------------------------
| STATS HELPERS
|--------------------------------------------------------------------------
*/

function ensureCategory(array &$stats, string $category): void
{
    if (!isset($stats['categories'][$category]))
    {
        $stats['categories'][$category] = [
            'total' => 0,
            'success' => 0,
            'fail' => 0,
            'warn' => 0,
            'duration' => 0.0,
        ];
    }
}

function addResult(array &$stats, string $category, string $status, float $duration): void
{
    ensureCategory($stats, $category);

    $stats['global']['total']++;
    $stats['global']['duration'] += $duration;

    $stats['categories'][$category]['total']++;
    $stats['categories'][$category]['duration'] += $duration;

    if ($status === 'success')
    {
        $stats['global']['success']++;
        $stats['categories'][$category]['success']++;
        return;
    }

    if ($status === 'fail')
    {
        $stats['global']['fail']++;
        $stats['categories'][$category]['fail']++;
        return;
    }

    if ($status === 'warn')
    {
        $stats['global']['warn']++;
        $stats['categories'][$category]['warn']++;
    }
}

/*
|--------------------------------------------------------------------------
| REGISTRE DES TESTS
|--------------------------------------------------------------------------
*/

function addGetTest(array &$tests, array $test): void
{
    $tests[] = $test;
}

function addHtmlCheck(array &$htmlChecks, array $check): void
{
    $htmlChecks[] = $check;
}

function addPostCheck(array &$postChecks, array $check): void
{
    $postChecks[] = $check;
}

/*
|--------------------------------------------------------------------------
| HTTP
|--------------------------------------------------------------------------
*/

function requestUrl(
    string $url,
    string $method = 'GET',
    array $headers = [],
    ?string $content = null
): array
{
    $baseHeaders = [
        'User-Agent: LoliSSR-TestRunner',
    ];

    $finalHeaders = array_merge($baseHeaders, $headers);

    $options = [
        'http' => [
            'method' => $method,
            'ignore_errors' => true,
            'follow_location' => 0,
            'max_redirects' => 0,
            'timeout' => 15,
            'header' => implode("\r\n", $finalHeaders) . "\r\n",
        ],
    ];

    if ($content !== null)
    {
        $options['http']['content'] = $content;
    }

    $context = stream_context_create($options);

    $body = @file_get_contents($url, false, $context);
    $headersOut = $http_response_header ?? [];

    $status = 0;
    $location = null;

    if (!empty($headersOut[0]) && preg_match('/\s(\d{3})\s/', $headersOut[0], $matches))
    {
        $status = (int) $matches[1];
    }

    foreach ($headersOut as $header)
    {
        if (stripos($header, 'Location:') === 0)
        {
            $location = trim(substr($header, 9));
            break;
        }
    }

    return [
        'status' => $status,
        'body' => is_string($body) ? $body : '',
        'headers' => $headersOut,
        'location' => $location,
        'url' => $url,
        'method' => $method,
    ];
}

/*
|--------------------------------------------------------------------------
| HTML HELPERS
|--------------------------------------------------------------------------
*/

function containsAll(string $html, array $needles): array
{
    foreach ($needles as $needle)
    {
        if (stripos($html, $needle) === false)
        {
            return [false, $needle];
        }
    }

    return [true, null];
}

function extractTitle(string $html): ?string
{
    if (preg_match('/<title[^>]*>(.*?)<\/title>/is', $html, $matches))
    {
        return trim(strip_tags($matches[1]));
    }

    return null;
}

function countOccurrences(string $html, string $needle): int
{
    return substr_count(strtolower($html), strtolower($needle));
}

/*
|--------------------------------------------------------------------------
| ASSERTIONS
|--------------------------------------------------------------------------
*/

function runGetTest(string $base, array $test): array
{
    $start = microtime(true);

    $url = $base . $test['path'];
    $response = requestUrl($url);

    $expectedStatus = $test['expected_status'];
    $status = $response['status'];
    $body = $response['body'];

    if ($status !== $expectedStatus)
    {
        return [
            'ok' => false,
            'message' => "status $status attendu $expectedStatus",
            'duration' => microtime(true) - $start,
        ];
    }

    if (!empty($test['must_contain']))
    {
        [$containsAllNeedles, $missingNeedle] = containsAll($body, $test['must_contain']);

        if (!$containsAllNeedles)
        {
            return [
                'ok' => false,
                'message' => 'texte absent : "' . $missingNeedle . '"',
                'duration' => microtime(true) - $start,
            ];
        }
    }

    if (!empty($test['expected_location_contains']))
    {
        $location = $response['location'] ?? '';

        if ($location === '' || stripos($location, $test['expected_location_contains']) === false)
        {
            return [
                'ok' => false,
                'message' => 'redirect location invalide',
                'duration' => microtime(true) - $start,
            ];
        }
    }

    return [
        'ok' => true,
        'message' => (string) $status,
        'duration' => microtime(true) - $start,
    ];
}

function runCallableCheck(callable $callback): array
{
    $start = microtime(true);
    $result = $callback();
    $result['duration'] = $result['duration'] ?? (microtime(true) - $start);

    return $result;
}

/*
|--------------------------------------------------------------------------
| EXPORT HTML
|--------------------------------------------------------------------------
*/

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function statusBadgeClass(string $status): string
{
    return match ($status) {
        'success' => 'badge-success',
        'fail' => 'badge-fail',
        'warn' => 'badge-warn',
        default => 'badge-default',
    };
}

function statusLabel(string $status): string
{
    return match ($status) {
        'success' => 'OK',
        'fail' => 'FAIL',
        'warn' => 'WARN',
        default => strtoupper($status),
    };
}

function buildIssueCards(array $items, string $type): string
{
    if (empty($items))
    {
        if ($type === 'fail')
        {
            return '<div class="card success-block">Aucun fail 🎉</div>';
        }

        return '<div class="card success-block">Aucun warning ✅</div>';
    }

    $html = '';

    foreach ($items as $item)
    {
        $class = $type === 'fail' ? 'fail-block' : 'warn-block';
        $urlHtml = '';

        if (!empty($item['url']))
        {
            $urlHtml = '<p><strong>URL :</strong> <a class="url-link" href="'
                . h($item['url'])
                . '" target="_blank" rel="noopener noreferrer">'
                . h($item['url'])
                . '</a></p>';
        }

        $html .= '<div class="card ' . h($class) . '">';
        $html .= '<h3>' . h($item['category'] . ' :: ' . $item['label']) . '</h3>';
        $html .= '<p><strong>Message :</strong> ' . h($item['message']) . '</p>';
        $html .= $urlHtml;
        $html .= '<p><strong>Durée :</strong> ' . h(formatDuration((float) $item['duration'])) . '</p>';
        $html .= '</div>';
    }

    return $html;
}

function buildHtmlReport(
    array $stats,
    array $allResults,
    array $failures,
    array $warnings,
    string $base,
    string $realSlug,
    int $realNumero,
    float $totalDuration
): string
{
    $rows = '';

    foreach ($allResults as $index => $result)
    {
        $urlCell = '<span class="muted">—</span>';

        if (!empty($result['url']))
        {
            $urlCell = '<a class="url-link" href="' . h($result['url']) . '" target="_blank" rel="noopener noreferrer">'
                . h($result['url'])
                . '</a>';
        }

        $rows .= '<tr data-index="' . h((string) $index) . '" data-status="' . h($result['status']) . '" data-duration="' . h((string) $result['duration']) . '">';
        $rows .= '<td><span class="badge ' . h(statusBadgeClass($result['status'])) . '">' . h(statusLabel($result['status'])) . '</span></td>';
        $rows .= '<td>' . h($result['category']) . '</td>';
        $rows .= '<td>' . h($result['label']) . '</td>';
        $rows .= '<td>' . $urlCell . '</td>';
        $rows .= '<td>' . h($result['message']) . '</td>';
        $rows .= '<td>' . h(formatDuration((float) $result['duration'])) . '</td>';
        $rows .= '</tr>';
    }

    $categoryCards = '';

    foreach ($stats['categories'] as $category => $categoryStats)
    {
        $categoryCards .= '<div class="card small">';
        $categoryCards .= '<h3>' . h($category) . '</h3>';
        $categoryCards .= '<div class="stats-mini">';
        $categoryCards .= '<span class="mini ok">OK: ' . h((string) $categoryStats['success']) . '</span>';
        $categoryCards .= '<span class="mini fail">FAIL: ' . h((string) $categoryStats['fail']) . '</span>';
        $categoryCards .= '<span class="mini warn">WARN: ' . h((string) $categoryStats['warn']) . '</span>';
        $categoryCards .= '</div>';
        $categoryCards .= '<p><strong>Total :</strong> ' . h((string) $categoryStats['total']) . '</p>';
        $categoryCards .= '<p><strong>Temps :</strong> ' . h(formatDuration((float) $categoryStats['duration'])) . '</p>';
        $categoryCards .= '</div>';
    }

    $failList = buildIssueCards($failures, 'fail');
    $warnList = buildIssueCards($warnings, 'warn');

    $globalStatus = $stats['global']['fail'] === 0
        ? 'SUITE VALIDÉE'
        : 'SUITE À CORRIGER';

    $globalClass = $stats['global']['fail'] === 0
        ? 'hero-success'
        : 'hero-fail';

    return '<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>LoliSSR Test Report</title>
    <style>
        body{margin:0;padding:32px;font-family:"Segoe UI",Arial,sans-serif;background:#0b0b12;color:#f5f5ff}
        .hero,.card{background:#141420;border:1px solid rgba(255,255,255,.08);border-radius:18px;padding:18px;margin-bottom:18px}
        .grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px}
        .badge{display:inline-block;padding:5px 10px;border-radius:999px;font-size:12px;font-weight:800}
        .badge-success{background:rgba(34,197,94,.18);color:#86efac}
        .badge-fail{background:rgba(239,68,68,.18);color:#fca5a5}
        .badge-warn{background:rgba(245,158,11,.18);color:#fcd34d}
        .badge-default{background:rgba(148,163,184,.18);color:#cbd5e1}
        .url-link{color:#c44cff;text-decoration:none}
        table{width:100%;border-collapse:collapse;background:#141420;border:1px solid rgba(255,255,255,.08)}
        th,td{padding:12px;border-bottom:1px solid rgba(255,255,255,.05);text-align:left;vertical-align:top}
        th{background:rgba(123,44,255,.12)}
        .mini{display:inline-block;padding:6px 10px;border-radius:999px;font-size:12px;font-weight:700}
        .ok{background:rgba(34,197,94,.15);color:#86efac}
        .fail{background:rgba(239,68,68,.15);color:#fca5a5}
        .warn{background:rgba(245,158,11,.15);color:#fcd34d}
    </style>
</head>
<body>
    <div class="hero ' . h($globalClass) . '">
        <h1>' . h($globalStatus) . '</h1>
        <p><strong>Date :</strong> ' . h(date('Y-m-d H:i:s')) . '</p>
        <p><strong>Base URL :</strong> ' . h($base) . '</p>
        <p><strong>Slug :</strong> ' . h($realSlug) . '</p>
        <p><strong>Numero :</strong> ' . h((string) $realNumero) . '</p>
        <p><strong>Temps global :</strong> ' . h(formatDuration($totalDuration)) . '</p>
    </div>

    <div class="grid">
        <div class="card"><h3>Tests exécutés</h3><p>' . h((string) $stats['global']['total']) . '</p></div>
        <div class="card"><h3>Succès</h3><p>' . h((string) $stats['global']['success']) . '</p></div>
        <div class="card"><h3>Échecs</h3><p>' . h((string) $stats['global']['fail']) . '</p></div>
        <div class="card"><h3>Warnings</h3><p>' . h((string) $stats['global']['warn']) . '</p></div>
    </div>

    <div class="grid">' . $categoryCards . '</div>

    <table>
        <thead>
            <tr>
                <th>Statut</th>
                <th>Catégorie</th>
                <th>Test</th>
                <th>URL</th>
                <th>Message</th>
                <th>Durée</th>
            </tr>
        </thead>
        <tbody>' . $rows . '</tbody>
    </table>

    <h2>Fails</h2>' . $failList . '
    <h2>Warnings</h2>' . $warnList . '
</body>
</html>';
}