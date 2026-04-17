<?php

declare(strict_types=1);

$base = 'http://localhost/lolissr';

/*
|--------------------------------------------------------------------------
| CONFIG
|--------------------------------------------------------------------------
*/

$realSlug = 'one-piece';
$realNumero = 1;
$nonCanonicalSlug = 'One-Piece';

/*
|--------------------------------------------------------------------------
| OPTIONS
|--------------------------------------------------------------------------
*/

$testCanonicalRedirect = true;
$testPostAjouter = false;
$testPostUpdate = false;

/*
|--------------------------------------------------------------------------
| EXPORT
|--------------------------------------------------------------------------
*/

$exportDirectory = __DIR__ . '/reports';
$exportEnabled = true;

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
| HELPERS AFFICHAGE
|--------------------------------------------------------------------------
*/

$plainOutput = '';
$allResults = [];

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
| STATS
|--------------------------------------------------------------------------
*/

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
| CONFIG TESTS GET
|--------------------------------------------------------------------------
*/

$tests = [
    [
        'category' => 'Pages principales',
        'label' => 'Accueil',
        'path' => '/',
        'expected_status' => 200,
        'must_contain' => ['<body'],
    ],
    [
        'category' => 'Pages principales',
        'label' => 'Dashboard manga',
        'path' => '/manga',
        'expected_status' => 200,
        'must_contain' => ['Manga'],
    ],
    [
        'category' => 'Pages principales',
        'label' => 'Collection',
        'path' => '/manga/collection',
        'expected_status' => 200,
        'must_contain' => ['Collection'],
    ],
    [
        'category' => 'Pages principales',
        'label' => 'Pagination collection page 2',
        'path' => '/manga/collection/page/2',
        'expected_status' => 200,
    ],
    [
        'category' => 'Pages principales',
        'label' => 'Recherche',
        'path' => '/manga/recherche',
        'expected_status' => 200,
    ],
    [
        'category' => 'Pages principales',
        'label' => 'Ajouter',
        'path' => '/manga/ajouter',
        'expected_status' => 200,
        'must_contain' => ['<form', 'Livre', 'slug', 'numero'],
    ],
    [
        'category' => 'Pages principales',
        'label' => 'Page lien',
        'path' => '/manga/lien',
        'expected_status' => 200,
    ],
    [
        'category' => 'Pages mangas',
        'label' => 'Série existante',
        'path' => '/manga/serie/' . $realSlug,
        'expected_status' => 200,
    ],
    [
        'category' => 'Pages mangas',
        'label' => 'Tome existant',
        'path' => '/manga/' . $realSlug . '/' . $realNumero,
        'expected_status' => 200,
    ],
    [
        'category' => 'Pages mangas',
        'label' => 'Page modifier',
        'path' => '/manga/update/' . $realSlug . '/' . $realNumero,
        'expected_status' => 200,
        'must_contain' => ['<form'],
    ],
    [
        'category' => 'Erreurs',
        'label' => '404 inexistante',
        'path' => '/page-introuvable',
        'expected_status' => 404,
    ],
];

if ($testCanonicalRedirect)
{
    $tests[] = [
        'category' => 'Canonical',
        'label' => 'Redirect canonique série',
        'path' => '/manga/serie/' . $nonCanonicalSlug,
        'expected_status' => 301,
        'expected_location_contains' => '/manga/serie/' . $realSlug,
    ];

    $tests[] = [
        'category' => 'Canonical',
        'label' => 'Redirect canonique tome',
        'path' => '/manga/' . $nonCanonicalSlug . '/' . $realNumero,
        'expected_status' => 301,
        'expected_location_contains' => '/manga/' . $realSlug . '/' . $realNumero,
    ];

    $tests[] = [
        'category' => 'Canonical',
        'label' => 'Redirect canonique modifier',
        'path' => '/manga/update/' . $nonCanonicalSlug . '/' . $realNumero,
        'expected_status' => 301,
        'expected_location_contains' => '/manga/update/' . $realSlug . '/' . $realNumero,
    ];
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
| ASSERTIONS GET
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

/*
|--------------------------------------------------------------------------
| CHECKS HTML
|--------------------------------------------------------------------------
*/

function runExtraChecks(string $base, string $realSlug, int $realNumero): array
{
    $checks = [];

    $start = microtime(true);
    $detail = requestUrl($base . '/manga/' . $realSlug . '/' . $realNumero);

    if ($detail['status'] === 200)
    {
        $title = extractTitle($detail['body']);

        $checks[] = [
            'category' => 'HTML',
            'label' => 'Detail a un <title>',
            'ok' => !empty($title),
            'message' => $title ?: 'title absent',
            'duration' => microtime(true) - $start,
            'url' => $base . '/manga/' . $realSlug . '/' . $realNumero,
        ];

        $start = microtime(true);

        $checks[] = [
            'category' => 'HTML',
            'label' => 'Detail contient au moins une image',
            'ok' => preg_match('/<img\b/i', $detail['body']) === 1,
            'message' => preg_match('/<img\b/i', $detail['body']) === 1 ? 'img trouvée' : 'aucune image',
            'duration' => microtime(true) - $start,
            'url' => $base . '/manga/' . $realSlug . '/' . $realNumero,
        ];

        $start = microtime(true);

        $checks[] = [
            'category' => 'HTML',
            'label' => 'Detail contient au moins un lien',
            'ok' => preg_match('/<a\b/i', $detail['body']) === 1,
            'message' => preg_match('/<a\b/i', $detail['body']) === 1 ? 'lien trouvé' : 'aucun lien',
            'duration' => microtime(true) - $start,
            'url' => $base . '/manga/' . $realSlug . '/' . $realNumero,
        ];
    }
    else
    {
        $checks[] = [
            'category' => 'HTML',
            'label' => 'Detail HTML check',
            'ok' => false,
            'message' => 'page détail inaccessible',
            'duration' => microtime(true) - $start,
            'url' => $base . '/manga/' . $realSlug . '/' . $realNumero,
        ];
    }

    $start = microtime(true);
    $ajouter = requestUrl($base . '/manga/ajouter');

    if ($ajouter['status'] === 200)
    {
        $requiredFields = [
            'name="livre"',
            'name="slug"',
            'name="numero"',
        ];

        foreach ($requiredFields as $field)
        {
            $fieldStart = microtime(true);

            $checks[] = [
                'category' => 'HTML',
                'label' => 'Ajouter contient ' . $field,
                'ok' => stripos($ajouter['body'], $field) !== false,
                'message' => stripos($ajouter['body'], $field) !== false ? 'ok' : 'absent',
                'duration' => microtime(true) - $fieldStart,
                'url' => $base . '/manga/ajouter',
            ];
        }
    }

    $start = microtime(true);
    $modifier = requestUrl($base . '/manga/update/' . $realSlug . '/' . $realNumero);

    if ($modifier['status'] === 200)
    {
        $possibleFields = [
            'name="jacquette"',
            'name="livre_note"',
            'name="commentaire"',
        ];

        foreach ($possibleFields as $field)
        {
            $fieldStart = microtime(true);

            $checks[] = [
                'category' => 'HTML',
                'label' => 'Modifier contient ' . $field,
                'ok' => stripos($modifier['body'], $field) !== false,
                'message' => stripos($modifier['body'], $field) !== false ? 'ok' : 'absent',
                'duration' => microtime(true) - $fieldStart,
                'url' => $base . '/manga/update/' . $realSlug . '/' . $realNumero,
            ];
        }
    }

    $start = microtime(true);
    $collection = requestUrl($base . '/manga/collection');

    if ($collection['status'] === 200)
    {
        $linkCount = countOccurrences($collection['body'], '<a');

        $checks[] = [
            'category' => 'HTML',
            'label' => 'Collection contient plusieurs liens',
            'ok' => $linkCount >= 3,
            'message' => $linkCount . ' lien(s)',
            'duration' => microtime(true) - $start,
            'url' => $base . '/manga/collection',
        ];
    }

    return $checks;
}

/*
|--------------------------------------------------------------------------
| POST TESTS
|--------------------------------------------------------------------------
*/

function runPostTests(
    string $base,
    string $realSlug,
    int $realNumero,
    bool $testPostAjouter,
    bool $testPostUpdate
): array
{
    $checks = [];

    if ($testPostAjouter)
    {
        $start = microtime(true);

        $payload = http_build_query([
            'livre' => 'Test Manga Auto',
            'slug' => 'test-manga-auto',
            'numero' => 999,
            'commentaire' => 'test auto'
        ]);

        $url = $base . '/manga/ajouter';

        $response = requestUrl(
            $url,
            'POST',
            [
                'Content-Type: application/x-www-form-urlencoded',
                'X-Requested-With: XMLHttpRequest',
            ],
            $payload
        );

        $checks[] = [
            'category' => 'POST',
            'label' => 'POST ajouter validation AJAX',
            'ok' => in_array($response['status'], [200, 422], true),
            'message' => 'status ' . $response['status'],
            'duration' => microtime(true) - $start,
            'url' => $url,
        ];
    }
    else
    {
        $checks[] = [
            'category' => 'POST',
            'label' => 'POST ajouter',
            'ok' => true,
            'message' => 'skippé (option désactivée)',
            'warn' => true,
            'duration' => 0.0,
            'url' => $base . '/manga/ajouter',
        ];
    }

    if ($testPostUpdate)
    {
        $start = microtime(true);

        $payload = http_build_query([
            'jacquette' => '5',
            'livre_note' => '5',
            'commentaire' => 'Test update auto'
        ]);

        $url = $base . '/manga/ajax/update-note/' . $realSlug . '/' . $realNumero;

        $response = requestUrl(
            $url,
            'POST',
            [
                'Content-Type: application/x-www-form-urlencoded',
                'X-Requested-With: XMLHttpRequest',
            ],
            $payload
        );

        $json = json_decode($response['body'], true);

        $checks[] = [
            'category' => 'POST',
            'label' => 'POST ajax update note',
            'ok' => $response['status'] === 200 && is_array($json) && !empty($json['success']),
            'message' => 'status ' . $response['status'],
            'duration' => microtime(true) - $start,
            'url' => $url,
        ];
    }
    else
    {
        $checks[] = [
            'category' => 'POST',
            'label' => 'POST update',
            'ok' => true,
            'message' => 'skippé (option désactivée)',
            'warn' => true,
            'duration' => 0.0,
            'url' => $base . '/manga/ajax/update-note/' . $realSlug . '/' . $realNumero,
        ];
    }

    return $checks;
}

/*
|--------------------------------------------------------------------------
| HTML EXPORT
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
        :root
        {
            --bg: #0b0b12;
            --surface: #141420;
            --surface-2: #1c1c2b;
            --border: rgba(255,255,255,0.08);

            --text: #f5f5ff;
            --text-soft: #cfcfe8;
            --text-muted: #9fa3b8;

            --violet: #7b2cff;
            --violet-2: #c44cff;

            --green: #22c55e;
            --red: #ef4444;
            --yellow: #f59e0b;

            --shadow: 0 18px 40px rgba(0, 0, 0, 0.35);
            --radius: 18px;
        }

        *
        {
            box-sizing: border-box;
        }

        body
        {
            margin: 0;
            padding: 32px;
            font-family: "Segoe UI", Arial, sans-serif;
            background:
                radial-gradient(circle at top left, rgba(123, 44, 255, 0.18), transparent 28%),
                radial-gradient(circle at top right, rgba(196, 76, 255, 0.14), transparent 24%),
                var(--bg);
            color: var(--text);
        }

        h1, h2, h3
        {
            margin-top: 0;
        }

        p
        {
            margin: 8px 0;
            color: var(--text-soft);
        }

        .hero
        {
            padding: 28px;
            border-radius: 24px;
            margin-bottom: 28px;
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
            background: linear-gradient(
                135deg,
                rgba(123, 44, 255, 0.24),
                rgba(196, 76, 255, 0.14)
            );
        }

        .hero-success
        {
            outline: 1px solid rgba(34, 197, 94, 0.22);
        }

        .hero-fail
        {
            outline: 1px solid rgba(239, 68, 68, 0.25);
        }

        .hero h1
        {
            font-size: 2rem;
            margin-bottom: 10px;
        }

        .meta
        {
            color: var(--text-soft);
            margin-top: 8px;
            line-height: 1.8;
        }

        .grid
        {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 16px;
            margin-bottom: 28px;
        }

        .card
        {
            background: linear-gradient(
                180deg,
                rgba(255,255,255,0.03),
                rgba(255,255,255,0.015)
            );
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 18px;
            box-shadow: var(--shadow);
        }

        .small
        {
            min-height: 150px;
        }

        .success-block
        {
            border-left: 5px solid var(--green);
        }

        .fail-block
        {
            border-left: 5px solid var(--red);
        }

        .warn-block
        {
            border-left: 5px solid var(--yellow);
        }

        .stats-mini
        {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-bottom: 12px;
        }

        .mini
        {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
        }

        .mini.ok
        {
            background: rgba(34, 197, 94, 0.15);
            color: #86efac;
        }

        .mini.fail
        {
            background: rgba(239, 68, 68, 0.15);
            color: #fca5a5;
        }

        .mini.warn
        {
            background: rgba(245, 158, 11, 0.15);
            color: #fcd34d;
        }

        .section
        {
            margin-top: 34px;
        }

        .section-header
        {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
            margin-bottom: 16px;
        }

        .section-header h2
        {
            margin-bottom: 0;
        }

        .controls
        {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .controls button,
        .controls select
        {
            background: var(--surface-2);
            color: var(--text);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 10px 12px;
            font-size: 14px;
            cursor: pointer;
        }

        .controls button:hover,
        .controls select:hover
        {
            border-color: rgba(123, 44, 255, 0.55);
        }

        table
        {
            width: 100%;
            border-collapse: collapse;
            background: rgba(20,20,32,0.92);
            border-radius: 18px;
            overflow: hidden;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
        }

        th, td
        {
            padding: 13px 14px;
            border-bottom: 1px solid rgba(255,255,255,0.05);
            text-align: left;
            vertical-align: top;
        }

        th
        {
            background: linear-gradient(
                180deg,
                rgba(123, 44, 255, 0.18),
                rgba(123, 44, 255, 0.08)
            );
            color: var(--text);
        }

        tr:hover td
        {
            background: rgba(255,255,255,0.02);
        }

        .badge
        {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 0.02em;
        }

        .badge-success
        {
            background: rgba(34, 197, 94, 0.18);
            color: #86efac;
        }

        .badge-fail
        {
            background: rgba(239, 68, 68, 0.18);
            color: #fca5a5;
        }

        .badge-warn
        {
            background: rgba(245, 158, 11, 0.18);
            color: #fcd34d;
        }

        .badge-default
        {
            background: rgba(148, 163, 184, 0.18);
            color: #cbd5e1;
        }

        .muted
        {
            color: var(--text-muted);
        }

        .footer
        {
            margin-top: 38px;
            color: var(--text-muted);
            font-size: 14px;
        }

        .url-link
        {
            color: var(--violet-2);
            text-decoration: none;
            font-weight: 600;
            word-break: break-all;
        }

        .url-link:hover
        {
            color: #e19bff;
            text-decoration: underline;
        }

        @media (max-width: 900px)
        {
            body
            {
                padding: 18px;
            }

            table, thead, tbody, th, td, tr
            {
                display: block;
            }

            thead
            {
                display: none;
            }

            tr
            {
                margin-bottom: 14px;
                border: 1px solid var(--border);
                border-radius: 14px;
                overflow: hidden;
            }

            td
            {
                border-bottom: 1px solid rgba(255,255,255,0.05);
            }
        }
    </style>
</head>
<body>
    <div class="hero ' . h($globalClass) . '">
        <h1>' . h($globalStatus) . '</h1>

        <div class="meta">
            <div><strong>Date :</strong> ' . h(date('Y-m-d H:i:s')) . '</div>
            <div><strong>Base URL :</strong> ' . h($base) . '</div>
            <div><strong>Slug :</strong> ' . h($realSlug) . '</div>
            <div><strong>Numero :</strong> ' . h((string) $realNumero) . '</div>
            <div><strong>Temps global :</strong> ' . h(formatDuration($totalDuration)) . '</div>
        </div>
    </div>

    <div class="grid">
        <div class="card">
            <h3>Tests exécutés</h3>
            <p>' . h((string) $stats['global']['total']) . '</p>
        </div>

        <div class="card">
            <h3>Succès</h3>
            <p>' . h((string) $stats['global']['success']) . '</p>
        </div>

        <div class="card">
            <h3>Échecs</h3>
            <p>' . h((string) $stats['global']['fail']) . '</p>
        </div>

        <div class="card">
            <h3>Warnings</h3>
            <p>' . h((string) $stats['global']['warn']) . '</p>
        </div>

        <div class="card">
            <h3>Temps cumulé</h3>
            <p>' . h(formatDuration((float) $stats['global']['duration'])) . '</p>
        </div>
    </div>

    <div class="section">
        <div class="section-header">
            <h2>Résumé par catégorie</h2>
        </div>

        <div class="grid">
            ' . $categoryCards . '
        </div>
    </div>

    <div class="section">
        <div class="section-header">
            <h2>Résultats détaillés</h2>

            <div class="controls">
                <button type="button" onclick="filterRows(\'all\')">Tous</button>
                <button type="button" onclick="filterRows(\'success\')">OK</button>
                <button type="button" onclick="filterRows(\'fail\')">FAIL</button>
                <button type="button" onclick="filterRows(\'warn\')">WARN</button>

                <select id="sortSelect" onchange="sortRows(this.value)">
                    <option value="default">Tri par défaut</option>
                    <option value="duration_desc">Durée ↓</option>
                    <option value="duration_asc">Durée ↑</option>
                    <option value="status">Statut</option>
                    <option value="category">Catégorie</option>
                </select>
            </div>
        </div>

        <table id="resultsTable">
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
            <tbody>
                ' . $rows . '
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2>Fails</h2>
        ' . $failList . '
    </div>

    <div class="section">
        <h2>Warnings</h2>
        ' . $warnList . '
    </div>

    <div class="footer">
        Rapport généré automatiquement par tests/run-tests.php
    </div>

    <script>
        const tbody = document.querySelector("#resultsTable tbody");
        const originalRows = Array.from(tbody.querySelectorAll("tr"));

        function filterRows(status)
        {
            const rows = tbody.querySelectorAll("tr");

            rows.forEach((row) =>
            {
                if (status === "all")
                {
                    row.style.display = "";
                    return;
                }

                row.style.display = row.dataset.status === status ? "" : "none";
            });
        }

        function sortRows(mode)
        {
            let rows = Array.from(tbody.querySelectorAll("tr"));

            if (mode === "default")
            {
                rows = Array.from(originalRows);
            }
            else if (mode === "duration_desc")
            {
                rows.sort((a, b) => parseFloat(b.dataset.duration) - parseFloat(a.dataset.duration));
            }
            else if (mode === "duration_asc")
            {
                rows.sort((a, b) => parseFloat(a.dataset.duration) - parseFloat(b.dataset.duration));
            }
            else if (mode === "status")
            {
                rows.sort((a, b) => a.dataset.status.localeCompare(b.dataset.status));
            }
            else if (mode === "category")
            {
                rows.sort((a, b) => a.children[1].innerText.localeCompare(b.children[1].innerText));
            }

            tbody.innerHTML = "";
            rows.forEach((row) => tbody.appendChild(row));
        }
    </script>
</body>
</html>';
}

/*
|--------------------------------------------------------------------------
| RUNNER
|--------------------------------------------------------------------------
*/

$globalStart = microtime(true);

sectionTitle('TESTS LOLISSR');

$currentCategory = null;

foreach ($tests as $test)
{
    $category = $test['category'];

    if ($currentCategory !== $category)
    {
        $currentCategory = $category;
        categoryTitle($category);
    }

    $result = runGetTest($base, $test);
    $duration = $result['duration'] ?? 0.0;
    $testUrl = $base . $test['path'];

    if ($result['ok'])
    {
        printOk($test['label'] . ' -> ' . $test['path'], $result['message'], $duration);
        addResult($stats, $category, 'success', $duration);

        recordResult(
            'success',
            $category,
            $test['label'] . ' -> ' . $test['path'],
            $result['message'],
            $duration,
            $testUrl
        );
    }
    else
    {
        printFail($test['label'] . ' -> ' . $test['path'], $result['message'], $duration);
        addResult($stats, $category, 'fail', $duration);

        recordResult(
            'fail',
            $category,
            $test['label'] . ' -> ' . $test['path'],
            $result['message'],
            $duration,
            $testUrl
        );

        $failures[] = [
            'category' => $category,
            'label' => $test['label'],
            'message' => $result['message'],
            'duration' => $duration,
            'url' => $testUrl,
        ];
    }
}

sectionTitle('CHECKS HTML / STRUCTURE');

$htmlChecks = runExtraChecks($base, $realSlug, $realNumero);
$currentCategory = null;

foreach ($htmlChecks as $check)
{
    $category = $check['category'];

    if ($currentCategory !== $category)
    {
        $currentCategory = $category;
        categoryTitle($category);
    }

    $duration = $check['duration'] ?? 0.0;
    $checkUrl = $check['url'] ?? null;

    if ($check['ok'])
    {
        printOk($check['label'], $check['message'], $duration);
        addResult($stats, $category, 'success', $duration);

        recordResult(
            'success',
            $category,
            $check['label'],
            $check['message'],
            $duration,
            $checkUrl
        );
    }
    else
    {
        printFail($check['label'], $check['message'], $duration);
        addResult($stats, $category, 'fail', $duration);

        recordResult(
            'fail',
            $category,
            $check['label'],
            $check['message'],
            $duration,
            $checkUrl
        );

        $failures[] = [
            'category' => $category,
            'label' => $check['label'],
            'message' => $check['message'],
            'duration' => $duration,
            'url' => $checkUrl,
        ];
    }
}

sectionTitle('TESTS POST');

$postChecks = runPostTests(
    $base,
    $realSlug,
    $realNumero,
    $testPostAjouter,
    $testPostUpdate
);

$currentCategory = null;

foreach ($postChecks as $check)
{
    $category = $check['category'];

    if ($currentCategory !== $category)
    {
        $currentCategory = $category;
        categoryTitle($category);
    }

    $duration = $check['duration'] ?? 0.0;
    $checkUrl = $check['url'] ?? null;

    if (!empty($check['warn']))
    {
        printWarn($check['label'], $check['message'], $duration);
        addResult($stats, $category, 'warn', $duration);

        recordResult(
            'warn',
            $category,
            $check['label'],
            $check['message'],
            $duration,
            $checkUrl
        );

        $warnings[] = [
            'category' => $category,
            'label' => $check['label'],
            'message' => $check['message'],
            'duration' => $duration,
            'url' => $checkUrl,
        ];
        continue;
    }

    if ($check['ok'])
    {
        printOk($check['label'], $check['message'], $duration);
        addResult($stats, $category, 'success', $duration);

        recordResult(
            'success',
            $category,
            $check['label'],
            $check['message'],
            $duration,
            $checkUrl
        );
    }
    else
    {
        printFail($check['label'], $check['message'], $duration);
        addResult($stats, $category, 'fail', $duration);

        recordResult(
            'fail',
            $category,
            $check['label'],
            $check['message'],
            $duration,
            $checkUrl
        );

        $failures[] = [
            'category' => $category,
            'label' => $check['label'],
            'message' => $check['message'],
            'duration' => $duration,
            'url' => $checkUrl,
        ];
    }
}

/*
|--------------------------------------------------------------------------
| TRI AUTO FAILS
|--------------------------------------------------------------------------
*/

if (!empty($failures))
{
    usort($failures, static function (array $a, array $b): int
    {
        return $b['duration'] <=> $a['duration'];
    });

    sectionTitle('FAILS TRIÉS');

    foreach ($failures as $failure)
    {
        printFail(
            $failure['category'] . ' :: ' . $failure['label'],
            $failure['message'],
            $failure['duration']
        );
    }
}

/*
|--------------------------------------------------------------------------
| RÉSUMÉ PAR CATÉGORIE
|--------------------------------------------------------------------------
*/

sectionTitle('RÉSUMÉ PAR CATÉGORIE');

foreach ($stats['categories'] as $category => $categoryStats)
{
    $line = str_pad($category, 22);
    $line .= ' ';
    $line .= color('OK: ' . $categoryStats['success'], C_GREEN . C_BOLD);
    $line .= '   ';
    $line .= color('FAIL: ' . $categoryStats['fail'], C_RED . C_BOLD);
    $line .= '   ';
    $line .= color('WARN: ' . $categoryStats['warn'], C_YELLOW . C_BOLD);
    $line .= '   ';
    $line .= color('TOTAL: ' . $categoryStats['total'], C_CYAN . C_BOLD);
    $line .= '   ';
    $line .= color('TIME: ' . formatDuration($categoryStats['duration']), C_DIM);

    outLine($line);
}

/*
|--------------------------------------------------------------------------
| RÉSUMÉ FINAL PREMIUM
|--------------------------------------------------------------------------
*/

sectionTitle('RÉSUMÉ FINAL');

$g = $stats['global'];
$totalDuration = microtime(true) - $globalStart;

outLine(color(line('─'), C_DIM));
outLine(color('Tests exécutés : ', C_BOLD) . color((string) $g['total'], C_CYAN . C_BOLD));
outLine(color('Succès         : ', C_BOLD) . color((string) $g['success'], C_GREEN . C_BOLD));
outLine(color('Échecs         : ', C_BOLD) . color((string) $g['fail'], C_RED . C_BOLD));
outLine(color('Warnings       : ', C_BOLD) . color((string) $g['warn'], C_YELLOW . C_BOLD));
outLine(color('Temps cumulé   : ', C_BOLD) . color(formatDuration($g['duration']), C_DIM));
outLine(color('Temps global   : ', C_BOLD) . color(formatDuration($totalDuration), C_DIM));
outLine(color(line('─'), C_DIM));

if ($g['fail'] === 0)
{
    outLine(color('🏆 SUITE VALIDÉE', C_BG_GREEN . C_WHITE . C_BOLD));
}
else
{
    outLine(color('💥 SUITE À CORRIGER', C_BG_RED . C_WHITE . C_BOLD));
}

if ($g['fail'] === 0 && $g['warn'] === 0)
{
    outLine(color('État global : nickel, tout est vert.', C_GREEN . C_BOLD));
}
elseif ($g['fail'] === 0)
{
    outLine(color('État global : stable, avec quelques tests skippés.', C_YELLOW . C_BOLD));
}
else
{
    outLine(color('État global : il reste des points à corriger.', C_RED . C_BOLD));
}

/*
|--------------------------------------------------------------------------
| EXPORT TXT + HTML
|--------------------------------------------------------------------------
*/

if ($exportEnabled)
{
    if (!is_dir($exportDirectory) && !mkdir($exportDirectory, 0777, true) && !is_dir($exportDirectory))
    {
        outLine();
        outLine(color('❌ Impossible de créer le dossier de rapport : ' . $exportDirectory, C_RED . C_BOLD));
        exit(1);
    }

    $timestamp = date('Y-m-d_H-i-s');

    $txtFile = $exportDirectory . '/test-report-' . $timestamp . '.txt';
    $htmlFile = $exportDirectory . '/test-report-' . $timestamp . '.html';

    $exportHeader = '';
    $exportHeader .= "LoliSSR Test Report\n";
    $exportHeader .= line('=') . "\n";
    $exportHeader .= 'Date: ' . date('Y-m-d H:i:s') . "\n";
    $exportHeader .= 'Base URL: ' . $base . "\n";
    $exportHeader .= 'Slug: ' . $realSlug . "\n";
    $exportHeader .= 'Numero: ' . $realNumero . "\n";
    $exportHeader .= line('=') . "\n\n";

    if (file_put_contents($txtFile, $exportHeader . $plainOutput) === false)
    {
        outLine();
        outLine(color('❌ Impossible d’écrire le rapport TXT : ' . $txtFile, C_RED . C_BOLD));
        exit(1);
    }

    $htmlReport = buildHtmlReport(
        $stats,
        $allResults,
        $failures,
        $warnings,
        $base,
        $realSlug,
        $realNumero,
        $totalDuration
    );

    if (file_put_contents($htmlFile, $htmlReport) === false)
    {
        outLine();
        outLine(color('❌ Impossible d’écrire le rapport HTML : ' . $htmlFile, C_RED . C_BOLD));
        exit(1);
    }

    outLine();
    outLine(color('📄 Rapport TXT  : ' . $txtFile, C_CYAN . C_BOLD));
    outLine(color('🌐 Rapport HTML : ' . $htmlFile, C_CYAN . C_BOLD));
}

outLine();