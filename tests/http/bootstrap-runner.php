<?php

declare(strict_types=1);

if (!defined('ROOT'))
{
    define('ROOT', dirname(__DIR__, 2));
}

require_once ROOT . '/Autoloader.php';

\App\Autoloader::register();

require_once app_path('App/Core/Support/helpers.php');

/*
|--------------------------------------------------------------------------
| Chargement du fichier .env
|--------------------------------------------------------------------------
*/

$envFile = app_path('.env');

if (is_file($envFile))
{
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];

    foreach ($lines as $line)
    {
        $line = trim($line);

        if (
            $line === ''
            || str_starts_with($line, '#')
            || !str_contains($line, '=')
        )
        {
            continue;
        }

        [$name, $value] = explode('=', $line, 2);

        $name = trim($name);
        $value = trim($value);

        $_ENV[$name] = $value;
        $_SERVER[$name] = $value;
        putenv($name . '=' . $value);
    }
}

/*
|--------------------------------------------------------------------------
| CONFIG
|--------------------------------------------------------------------------
*/

$config = require app_path('tests/Http/config.php');

$base = (string) ($config['base'] ?? '');
$realSlug = (string) ($config['realSlug'] ?? '');
$realNumero = (int) ($config['realNumero'] ?? 0);
$nonCanonicalSlug = (string) ($config['nonCanonicalSlug'] ?? '');

$casesDirectory = (string) ($config['casesDirectory'] ?? '');
$fixturesDirectory = (string) ($config['fixturesDirectory'] ?? '');
$tmpUploadsDirectory = (string) ($config['tmpUploadsDirectory'] ?? '');
$exportDirectory = (string) ($config['exportDirectory'] ?? '');

$testAjaxUpdate = envBool($config['testAjaxUpdate'] ?? false);

$testCanonicalRedirect = envBool($config['testCanonicalRedirect'] ?? false);
$testPostAjouter = envBool($config['testPostAjouter'] ?? false);
$testPostUpdate = envBool($config['testPostUpdate'] ?? false);
$testUploadDuplicateSlugNumero = envBool($config['testUploadDuplicateSlugNumero'] ?? false);
$testUploadInvalidImage = envBool($config['testUploadInvalidImage'] ?? false);
$testUploadMaxSize = envBool($config['testUploadMaxSize'] ?? false);

$exportEnabled = envBool($config['exportEnabled'] ?? false);

/*
|--------------------------------------------------------------------------
| Garde-fou environnement
|--------------------------------------------------------------------------
|
| Les tests POST mutateurs ne doivent jamais tourner hors environnement
| testing, même si un bool a été activé par erreur.
|
*/

$appEnv = trim((string) (
    $_ENV['APP_ENV']
    ?? $_SERVER['APP_ENV']
    ?? getenv('APP_ENV')
    ?? ''
));

$isTestingEnv = ($appEnv === 'testing');

if (($testPostAjouter || $testPostUpdate) && !$isTestingEnv)
{
    throw new RuntimeException(
        'Tests POST mutateurs interdits hors environnement testing.'
    );
}

/*
|--------------------------------------------------------------------------
| HELPERS DOSSIERS
|--------------------------------------------------------------------------
*/

if (!function_exists('ensureDirectory'))
{
    function ensureDirectory(string $dir): void
    {
        if ($dir === '')
        {
            throw new RuntimeException('Chemin de dossier vide.');
        }

        if (is_dir($dir))
        {
            return;
        }

        if (!mkdir($dir, 0777, true) && !is_dir($dir))
        {
            throw new RuntimeException(
                'Impossible de créer le dossier : ' . $dir
            );
        }
    }
}

if (!function_exists('cleanTmpUploads'))
{
    function cleanTmpUploads(string $dir): void
    {
        ensureDirectory($dir);

        $files = glob(rtrim($dir, '/\\') . DIRECTORY_SEPARATOR . '*');

        if ($files === false)
        {
            return;
        }

        foreach ($files as $file)
        {
            if (is_file($file))
            {
                @unlink($file);
            }
        }
    }
}

ensureDirectory($exportDirectory);
ensureDirectory($tmpUploadsDirectory);
cleanTmpUploads($tmpUploadsDirectory);

/*
|--------------------------------------------------------------------------
| COULEURS TERMINAL
|--------------------------------------------------------------------------
*/

if (!defined('C_RESET'))   define('C_RESET', "\033[0m");
if (!defined('C_BOLD'))    define('C_BOLD', "\033[1m");
if (!defined('C_DIM'))     define('C_DIM', "\033[2m");

if (!defined('C_RED'))     define('C_RED', "\033[31m");
if (!defined('C_GREEN'))   define('C_GREEN', "\033[32m");
if (!defined('C_YELLOW'))  define('C_YELLOW', "\033[33m");
if (!defined('C_BLUE'))    define('C_BLUE', "\033[34m");
if (!defined('C_MAGENTA')) define('C_MAGENTA', "\033[35m");
if (!defined('C_CYAN'))    define('C_CYAN', "\033[36m");
if (!defined('C_WHITE'))   define('C_WHITE', "\033[37m");

if (!defined('C_BG_RED'))   define('C_BG_RED', "\033[41m");
if (!defined('C_BG_GREEN')) define('C_BG_GREEN', "\033[42m");

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

if (!function_exists('addPlain'))
{
    function addPlain(string $text): void
    {
        global $plainOutput;
        $plainOutput .= $text;
    }
}

if (!function_exists('stripAnsi'))
{
    function stripAnsi(string $text): string
    {
        return preg_replace('/\e\[[0-9;]*m/', '', $text) ?? $text;
    }
}

if (!function_exists('out'))
{
    function out(string $text): void
    {
        echo $text;
        addPlain(stripAnsi($text));
    }
}

if (!function_exists('outLine'))
{
    function outLine(string $text = ''): void
    {
        out($text . PHP_EOL);
    }
}

if (!function_exists('color'))
{
    function color(string $text, string $color): string
    {
        return $color . $text . C_RESET;
    }
}

if (!function_exists('line'))
{
    function line(string $char = '─', int $length = 72): string
    {
        return str_repeat($char, $length);
    }
}

if (!function_exists('sectionTitle'))
{
    function sectionTitle(string $title): void
    {
        outLine();
        outLine(color(line('═'), C_BLUE));
        outLine(color('  ' . $title, C_BOLD . C_CYAN));
        outLine(color(line('═'), C_BLUE));
        outLine();
    }
}

if (!function_exists('categoryTitle'))
{
    function categoryTitle(string $title): void
    {
        outLine();
        outLine(color('▶ ' . $title, C_BOLD . C_MAGENTA));
        outLine(color(line('─', 54), C_DIM));
    }
}

if (!function_exists('formatDuration'))
{
    function formatDuration(float $seconds): string
    {
        if ($seconds <= 0)
        {
            return '0 ms';
        }

        return number_format($seconds * 1000, 2, ',', ' ') . ' ms';
    }
}

if (!function_exists('printOk'))
{
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
}

if (!function_exists('printFail'))
{
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
}

if (!function_exists('printWarn'))
{
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
}

if (!function_exists('recordResult'))
{
    function recordResult(
        string $status,
        string $category,
        string $label,
        string $message,
        float $duration,
        ?string $url = null
    ): void {
        global $allResults;

        static $resultId = 1;

        $allResults[] = [
            'id' => $resultId++,
            'status' => $status,
            'category' => $category,
            'label' => $label,
            'message' => $message,
            'duration' => $duration,
            'url' => $url,
        ];
    }
}

if (!function_exists('decodeJsonResponse'))
{
    function decodeJsonResponse(string $body): ?array
    {
        $json = json_decode($body, true);

        return is_array($json) ? $json : null;
    }
}

/*
|--------------------------------------------------------------------------
| STATS HELPERS
|--------------------------------------------------------------------------
*/

if (!function_exists('ensureCategory'))
{
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
}

if (!function_exists('addResult'))
{
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
}

/*
|--------------------------------------------------------------------------
| REGISTRE DES TESTS
|--------------------------------------------------------------------------
*/

if (!function_exists('addGetTest'))
{
    function addGetTest(array &$tests, array $test): void
    {
        $tests[] = $test;
    }
}

if (!function_exists('addHtmlCheck'))
{
    function addHtmlCheck(array &$htmlChecks, array $check): void
    {
        $htmlChecks[] = $check;
    }
}

if (!function_exists('addPostCheck'))
{
    function addPostCheck(array &$postChecks, array $check): void
    {
        $postChecks[] = $check;
    }
}

/*
|--------------------------------------------------------------------------
| HTTP
|--------------------------------------------------------------------------
*/

if (!function_exists('requestUrl'))
{
    function requestUrl(
        string $url,
        string $method = 'GET',
        array $headers = [],
        ?string $content = null
    ): array {
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
                'timeout' => 10,
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

        if ($body === false && empty($headersOut))
        {
            return [
                'status' => 0,
                'body' => '',
                'headers' => [],
                'location' => null,
                'url' => $url,
                'method' => $method,
            ];
        }

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
}

/*
|--------------------------------------------------------------------------
| URL HELPERS
|--------------------------------------------------------------------------
*/

if (!function_exists('buildTestUrl'))
{
    function buildTestUrl(string $base, string $path): string
    {
        return rtrim($base, '/') . '/' . ltrim($path, '/');
    }
}

/*
|--------------------------------------------------------------------------
| MULTIPART
|--------------------------------------------------------------------------
*/

if (!function_exists('buildMultipartBody'))
{
    function buildMultipartBody(array $fields, array $files, string $boundary): string
    {
        $eol = "\r\n";
        $body = '';

        foreach ($fields as $name => $value)
        {
            $body .= '--' . $boundary . $eol;
            $body .= 'Content-Disposition: form-data; name="' . $name . '"' . $eol . $eol;
            $body .= (string) $value . $eol;
        }

        foreach ($files as $name => $file)
        {
            if (empty($file['path']) || !is_file($file['path']))
            {
                throw new RuntimeException('Fichier multipart introuvable : ' . ($file['path'] ?? 'null'));
            }

            $content = file_get_contents($file['path']);

            if ($content === false)
            {
                throw new RuntimeException('Impossible de lire le fichier : ' . $file['path']);
            }

            $filename = $file['filename'] ?? basename($file['path']);
            $type = $file['type'] ?? 'application/octet-stream';

            $body .= '--' . $boundary . $eol;
            $body .= 'Content-Disposition: form-data; name="' . $name . '"; filename="' . $filename . '"' . $eol;
            $body .= 'Content-Type: ' . $type . $eol . $eol;
            $body .= $content . $eol;
        }

        $body .= '--' . $boundary . '--' . $eol;

        return $body;
    }
}

/*
|--------------------------------------------------------------------------
| HTML HELPERS
|--------------------------------------------------------------------------
*/

if (!function_exists('containsAll'))
{
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
}

if (!function_exists('extractTitle'))
{
    function extractTitle(string $html): ?string
    {
        if (preg_match('/<title[^>]*>(.*?)<\/title>/is', $html, $matches))
        {
            return trim(strip_tags($matches[1]));
        }

        return null;
    }
}

if (!function_exists('countOccurrences'))
{
    function countOccurrences(string $html, string $needle): int
    {
        return substr_count(strtolower($html), strtolower($needle));
    }
}

/*
|--------------------------------------------------------------------------
| ASSERTIONS
|--------------------------------------------------------------------------
*/

if (!function_exists('runGetTest'))
{
    function runGetTest(string $base, array $test): array
    {
        $start = microtime(true);

        $url = rtrim($base, '/') . '/' . ltrim($test['path'], '/');
        $response = requestUrl($url);

        $expectedStatus = (int) $test['expected_status'];
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

            if (
                $location === ''
                || stripos($location, $test['expected_location_contains']) === false
            ) {
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
}

if (!function_exists('runCallableCheck'))
{
    function runCallableCheck(callable $callback): array
    {
        $start = microtime(true);
        $result = $callback();
        $result['duration'] = $result['duration'] ?? (microtime(true) - $start);

        return $result;
    }
}

/*
|--------------------------------------------------------------------------
| EXPORT HTML
|--------------------------------------------------------------------------
*/

if (!function_exists('h'))
{
    function h(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('statusBadgeClass'))
{
    function statusBadgeClass(string $status): string
    {
        return match ($status) {
            'success' => 'badge-success',
            'fail' => 'badge-fail',
            'warn' => 'badge-warn',
            default => 'badge-default',
        };
    }
}

if (!function_exists('statusLabel'))
{
    function statusLabel(string $status): string
    {
        return match ($status) {
            'success' => 'OK',
            'fail' => 'FAIL',
            'warn' => 'WARN',
            default => strtoupper($status),
        };
    }
}

if (!function_exists('buildIssueCards'))
{
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
}

if (!function_exists('buildHtmlReport'))
{
    function buildHtmlReport(
        array $stats,
        array $allResults,
        array $failures,
        array $warnings,
        string $base,
        string $realSlug,
        int $realNumero,
        float $totalDuration
    ): string {
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

            $rows .= '<tr data-index="' . h((string) $index) . '" data-status="' . h($result['status']) . '" data-category="' . h($result['category']) . '" data-duration="' . h((string) $result['duration']) . '">';
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
    $categoryCards .= '<button class="card small category-card" type="button" data-filter-category="' . h($category) . '">';
    $categoryCards .= '<h3>' . h($category) . '</h3>';
    $categoryCards .= '<div class="stats-mini">';
    $categoryCards .= '<span class="mini ok">OK: ' . h((string) $categoryStats['success']) . '</span>';
    $categoryCards .= '<span class="mini fail">FAIL: ' . h((string) $categoryStats['fail']) . '</span>';
    $categoryCards .= '<span class="mini warn">WARN: ' . h((string) $categoryStats['warn']) . '</span>';
    $categoryCards .= '</div>';
    $categoryCards .= '<p><strong>Total :</strong> ' . h((string) $categoryStats['total']) . '</p>';
    $categoryCards .= '<p><strong>Temps :</strong> ' . h(formatDuration((float) $categoryStats['duration'])) . '</p>';
    $categoryCards .= '</button>';
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
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>LoliSSR Test Report</title>
    <style>
:root{
    --bg:#0a0914;
    --bg-soft:#121022;
    --card:#151228;
    --card-2:#1a1630;
    --text:#f5f3ff;
    --muted:#b8accf;
    --border:rgba(255,255,255,.08);
    --violet:#7b2cff;
    --violet-2:#c44cff;
    --gradient:linear-gradient(135deg,#7b2cff 0%,#c44cff 100%);
    --ok:#22c55e;
    --fail:#ef4444;
    --warn:#f59e0b;
    --shadow:0 14px 40px rgba(0,0,0,.35);
    --shadow-violet:0 0 0 1px rgba(123,44,255,.22), 0 14px 40px rgba(123,44,255,.14);
}

*{box-sizing:border-box}

html{
    scroll-behavior:smooth;
}

body{
    margin:0;
    padding:32px;
    font-family:"Segoe UI",Arial,sans-serif;
    background:
        radial-gradient(circle at top left, rgba(123,44,255,.18), transparent 28%),
        radial-gradient(circle at top right, rgba(196,76,255,.14), transparent 26%),
        var(--bg);
    color:var(--text);
}

.hero,
.card{
    background:linear-gradient(180deg, rgba(255,255,255,.03), rgba(255,255,255,.015));
    border:1px solid var(--border);
    border-radius:24px;
    padding:20px;
    margin-bottom:18px;
    box-shadow:var(--shadow);
}

.hero{
    padding:28px;
}

.hero h1{
    margin:0 0 18px;
    font-size:52px;
    line-height:1;
    letter-spacing:.02em;
}

.hero-success{
    box-shadow:0 0 0 1px rgba(34,197,94,.20), 0 14px 40px rgba(34,197,94,.12);
}

.hero-fail{
    box-shadow:var(--shadow-violet);
}

.hero p{
    margin:10px 0;
    color:var(--text);
    font-size:17px;
}

.grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
    gap:16px;
}

.stats-grid{
    margin-bottom:18px;
}

.card h3{
    margin:0 0 12px;
    font-size:18px;
}

.card p{
    margin:0;
}

.stat-card,
.category-card{
    text-align:left;
    color:var(--text);
    cursor:pointer;
    transition:transform .18s ease, box-shadow .18s ease, border-color .18s ease;
}

.stat-card{
    background:none;
    width:100%;
}

.category-card{
    background:none;
    width:100%;
}

.stat-card:hover,
.category-card:hover{
    transform:translateY(-3px);
    box-shadow:var(--shadow-violet);
    border-color:rgba(196,76,255,.32);
}

.stat-card.is-active,
.category-card.is-active{
    background:linear-gradient(180deg, rgba(123,44,255,.18), rgba(255,255,255,.03));
    border-color:rgba(196,76,255,.45);
    box-shadow:var(--shadow-violet);
}

.stat-card p{
    font-size:38px;
    font-weight:800;
    color:#fff;
}

.stats-mini{
    display:flex;
    gap:8px;
    flex-wrap:wrap;
    margin-bottom:12px;
}

.mini,
.filter-pill,
.badge{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    padding:7px 12px;
    border-radius:999px;
    font-size:12px;
    font-weight:800;
    border:1px solid rgba(255,255,255,.06);
}

.badge-success,
.ok{
    background:rgba(34,197,94,.14);
    color:#9cf0b7;
}

.badge-fail,
.fail{
    background:rgba(239,68,68,.14);
    color:#ffb1b1;
}

.badge-warn,
.warn{
    background:rgba(245,158,11,.14);
    color:#ffd67d;
}

.badge-default{
    background:rgba(148,163,184,.14);
    color:#d7dcea;
}

.filter-pill{
    background:rgba(123,44,255,.12);
    color:#ead8ff;
}

.toolbar{
    display:flex;
    justify-content:space-between;
    gap:16px;
    align-items:center;
    flex-wrap:wrap;
}

.toolbar-left,
.toolbar-right{
    display:flex;
    align-items:center;
    gap:10px;
    flex-wrap:wrap;
}

.search-input{
    min-width:320px;
    max-width:100%;
    padding:12px 16px;
    border-radius:999px;
    border:1px solid rgba(255,255,255,.10);
    background:rgba(255,255,255,.04);
    color:#fff;
    outline:none;
}

.search-input:focus{
    border-color:rgba(196,76,255,.55);
    box-shadow:0 0 0 4px rgba(123,44,255,.14);
}

.reset-btn{
    border:none;
    border-radius:999px;
    padding:12px 16px;
    background:var(--gradient);
    color:#fff;
    font-weight:800;
    cursor:pointer;
    transition:transform .18s ease, opacity .18s ease;
}

.reset-btn:hover{
    transform:translateY(-2px);
    opacity:.96;
}

.results-counter{
    margin:18px 0 10px;
    color:var(--muted);
    font-size:14px;
}

.table-wrap{
    overflow:hidden;
    padding:0;
}

table{
    width:100%;
    border-collapse:collapse;
    background:transparent;
}

th,
td{
    padding:14px 16px;
    border-bottom:1px solid rgba(255,255,255,.05);
    text-align:left;
    vertical-align:top;
}

th{
    background:linear-gradient(180deg, rgba(123,44,255,.16), rgba(123,44,255,.08));
    color:#f6ebff;
    font-size:13px;
    text-transform:uppercase;
    letter-spacing:.06em;
}

tbody tr{
    transition:background .14s ease;
}

tbody tr:hover{
    background:rgba(255,255,255,.03);
}

tbody tr.is-hidden{
    display:none;
}

.url-link{
    color:#d169ff;
    text-decoration:none;
    word-break:break-all;
}

.url-link:hover{
    text-decoration:underline;
}

.muted{
    opacity:.6;
}

.success-block{
    border-color:rgba(34,197,94,.22);
}

.fail-block{
    border-color:rgba(239,68,68,.22);
}

.warn-block{
    border-color:rgba(245,158,11,.22);
}

.section-title{
    margin:28px 0 14px;
    font-size:24px;
}

.issue-grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(300px,1fr));
    gap:16px;
}

@media (max-width: 768px){
    body{
        padding:16px;
    }

    .hero h1{
        font-size:34px;
    }

    .stat-card p{
        font-size:30px;
    }

    .search-input{
        min-width:100%;
    }

    th,
    td{
        padding:10px;
        font-size:14px;
    }
}
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

    <div class="grid stats-grid">
        <button class="card stat-card is-active" type="button" data-filter-status="all">
            <h3>Tests exécutés</h3>
            <p>' . h((string) $stats['global']['total']) . '</p>
        </button>

        <button class="card stat-card" type="button" data-filter-status="success">
            <h3>Succès</h3>
            <p>' . h((string) $stats['global']['success']) . '</p>
        </button>

        <button class="card stat-card" type="button" data-filter-status="fail">
            <h3>Échecs</h3>
            <p>' . h((string) $stats['global']['fail']) . '</p>
        </button>

        <button class="card stat-card" type="button" data-filter-status="warn">
            <h3>Warnings</h3>
            <p>' . h((string) $stats['global']['warn']) . '</p>
        </button>
    </div>

    <div class="grid">' . $categoryCards . '</div>

    <div class="toolbar card">
        <div class="toolbar-left">
            <strong>Filtre actif :</strong>
            <span id="active-filter-status" class="filter-pill">Tous</span>
            <span id="active-filter-category" class="filter-pill">Toutes catégories</span>
        </div>

        <div class="toolbar-right">
            <input
                id="result-search"
                class="search-input"
                type="text"
                placeholder="Rechercher un test, une URL, un message..."
            >
            <button id="reset-filters" class="reset-btn" type="button">Réinitialiser</button>
        </div>
    </div>

    <p class="results-counter">
        <span id="visible-results-count">' . h((string) count($allResults)) . '</span>
        résultat(s) affiché(s)
    </p>

    <div class="table-wrap card">
        <table id="results-table">
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
    </div>

    <h2 id="fails-section" class="section-title">Fails</h2>
    <div class="issue-grid">' . $failList . '</div>

    <h2 id="warnings-section" class="section-title">Warnings</h2>
    <div class="issue-grid">' . $warnList . '</div>

    <script>
    (() => {
        const rows = Array.from(document.querySelectorAll("#results-table tbody tr"));
        const statCards = Array.from(document.querySelectorAll(".stat-card"));
        const categoryCards = Array.from(document.querySelectorAll(".category-card"));
        const searchInput = document.getElementById("result-search");
        const resetBtn = document.getElementById("reset-filters");
        const activeStatus = document.getElementById("active-filter-status");
        const activeCategory = document.getElementById("active-filter-category");
        const visibleCount = document.getElementById("visible-results-count");

        let currentStatus = "all";
        let currentCategory = "all";
        let currentSearch = "";

        function normalize(value) {
            return (value || "").toLowerCase().trim();
        }

        function updateCards() {
            statCards.forEach(card => {
                card.classList.toggle("is-active", card.dataset.filterStatus === currentStatus);
            });

            categoryCards.forEach(card => {
                card.classList.toggle("is-active", card.dataset.filterCategory === currentCategory);
            });
        }

        function updateLabels() {
            activeStatus.textContent =
                currentStatus === "all"
                    ? "Tous"
                    : currentStatus === "success"
                        ? "Succès"
                        : currentStatus === "fail"
                            ? "Échecs"
                            : "Warnings";

            activeCategory.textContent =
                currentCategory === "all"
                    ? "Toutes catégories"
                    : currentCategory;
        }

        function applyFilters() {
            let count = 0;

            rows.forEach(row => {
                const rowStatus = row.dataset.status || "";
                const rowCategory = row.dataset.category || "";
                const rowText = normalize(row.textContent);

                const matchStatus = currentStatus === "all" || rowStatus === currentStatus;
                const matchCategory = currentCategory === "all" || rowCategory === currentCategory;
                const matchSearch = currentSearch === "" || rowText.includes(currentSearch);

                const visible = matchStatus && matchCategory && matchSearch;

                row.classList.toggle("is-hidden", !visible);

                if (visible) {
                    count++;
                }
            });

            visibleCount.textContent = String(count);
            updateCards();
            updateLabels();
        }

        statCards.forEach(card => {
            card.addEventListener("click", () => {
                currentStatus = card.dataset.filterStatus || "all";
                applyFilters();

                if (currentStatus === "fail") {
                    const failTitle = document.getElementById("fails-section");

                    if (failTitle) {
                        failTitle.scrollIntoView({ behavior: "smooth", block: "start" });
                    }
                }
            });
        });

        categoryCards.forEach(card => {
            card.addEventListener("click", () => {
                const clickedCategory = card.dataset.filterCategory || "all";
                currentCategory = currentCategory === clickedCategory ? "all" : clickedCategory;
                applyFilters();
            });
        });

        if (searchInput) {
            searchInput.addEventListener("input", () => {
                currentSearch = normalize(searchInput.value);
                applyFilters();
            });
        }

        if (resetBtn) {
            resetBtn.addEventListener("click", () => {
                currentStatus = "all";
                currentCategory = "all";
                currentSearch = "";

                if (searchInput) {
                    searchInput.value = "";
                }

                applyFilters();
            });
        }

        applyFilters();
    })();
    </script>
</body>
</html>';
    }
}