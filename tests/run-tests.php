<?php

declare(strict_types=1);

require __DIR__ . '/Http/bootstrap-runner.php';

/*
|--------------------------------------------------------------------------
| CHARGEMENT AUTOMATIQUE DES CASES
|--------------------------------------------------------------------------
*/

$caseFiles = array_merge(
    glob($casesDirectory . '/safe/*.php') ?: [],
    glob($casesDirectory . '/mutateurs/*.php') ?: []
);

sort($caseFiles);

foreach ($caseFiles as $caseFile)
{
    require $caseFile;
}

/*
|--------------------------------------------------------------------------
| RUNNER GLOBAL
|--------------------------------------------------------------------------
*/

$globalStart = microtime(true);

sectionTitle('TESTS LOLISSR');

/*
|--------------------------------------------------------------------------
| RUNNER GET
|--------------------------------------------------------------------------
*/

$currentCategory = null;

foreach ($tests as $test)
{
    $category = (string) ($test['category'] ?? 'Sans catégorie');
    $label = (string) ($test['label'] ?? 'Test sans label');
    $path = (string) ($test['path'] ?? '/');

    if ($currentCategory !== $category)
    {
        $currentCategory = $category;
        categoryTitle($category);
    }

    $result = runGetTest($base, $test);
    $duration = (float) ($result['duration'] ?? 0.0);
    $message = (string) ($result['message'] ?? '');
    $testUrl = rtrim($base, '/') . '/' . ltrim($path, '/');

    if (!empty($result['ok']))
    {
        printOk($label . ' -> ' . $path, $message, $duration);
        addResult($stats, $category, 'success', $duration);
        recordResult('success', $category, $label . ' -> ' . $path, $message, $duration, $testUrl);
        continue;
    }

    printFail($label . ' -> ' . $path, $message, $duration);
    addResult($stats, $category, 'fail', $duration);
    recordResult('fail', $category, $label . ' -> ' . $path, $message, $duration, $testUrl);

    $failures[] = [
        'category' => $category,
        'label' => $label,
        'message' => $message,
        'duration' => $duration,
        'url' => $testUrl,
    ];
}

/*
|--------------------------------------------------------------------------
| RUNNER CHECKS
|--------------------------------------------------------------------------
*/

sectionTitle('CHECKS COMPLÉMENTAIRES');

$currentCategory = null;

foreach ($htmlChecks as $check)
{
    $category = (string) ($check['category'] ?? 'Sans catégorie');
    $label = (string) ($check['label'] ?? 'Check sans label');

    if ($currentCategory !== $category)
    {
        $currentCategory = $category;
        categoryTitle($category);
    }

    $result = isset($check['callback']) && is_callable($check['callback'])
        ? runCallableCheck($check['callback'])
        : ['ok' => false, 'warn' => false, 'message' => 'callback absente', 'duration' => 0.0];

    $duration = (float) ($result['duration'] ?? 0.0);
    $ok = (bool) ($result['ok'] ?? false);
    $warn = (bool) ($result['warn'] ?? false);
    $message = (string) ($result['message'] ?? '');
    $checkUrl = isset($check['url']) ? (string) $check['url'] : null;

    if ($warn)
    {
        printWarn($label, $message, $duration);
        addResult($stats, $category, 'warn', $duration);
        recordResult('warn', $category, $label, $message, $duration, $checkUrl);

        $warnings[] = [
            'category' => $category,
            'label' => $label,
            'message' => $message,
            'duration' => $duration,
            'url' => $checkUrl,
        ];

        continue;
    }

    if ($ok)
    {
        printOk($label, $message, $duration);
        addResult($stats, $category, 'success', $duration);
        recordResult('success', $category, $label, $message, $duration, $checkUrl);
        continue;
    }

    printFail($label, $message, $duration);
    addResult($stats, $category, 'fail', $duration);
    recordResult('fail', $category, $label, $message, $duration, $checkUrl);

    $failures[] = [
        'category' => $category,
        'label' => $label,
        'message' => $message,
        'duration' => $duration,
        'url' => $checkUrl,
    ];
}

/*
|--------------------------------------------------------------------------
| RUNNER POST
|--------------------------------------------------------------------------
*/

sectionTitle('TESTS POST');

$currentCategory = null;

foreach ($postChecks as $check)
{
    $category = (string) ($check['category'] ?? 'Sans catégorie');
    $label = (string) ($check['label'] ?? 'Check sans label');

    if ($currentCategory !== $category)
    {
        $currentCategory = $category;
        categoryTitle($category);
    }

    $result = isset($check['callback']) && is_callable($check['callback'])
        ? runCallableCheck($check['callback'])
        : ['ok' => false, 'warn' => false, 'message' => 'callback absente', 'duration' => 0.0];

    $duration = (float) ($result['duration'] ?? 0.0);
    $ok = (bool) ($result['ok'] ?? false);
    $warn = (bool) ($result['warn'] ?? false);
    $message = (string) ($result['message'] ?? '');
    $checkUrl = isset($check['url']) ? (string) $check['url'] : null;

    if ($warn)
    {
        printWarn($label, $message, $duration);
        addResult($stats, $category, 'warn', $duration);
        recordResult('warn', $category, $label, $message, $duration, $checkUrl);

        $warnings[] = [
            'category' => $category,
            'label' => $label,
            'message' => $message,
            'duration' => $duration,
            'url' => $checkUrl,
        ];

        continue;
    }

    if ($ok)
    {
        printOk($label, $message, $duration);
        addResult($stats, $category, 'success', $duration);
        recordResult('success', $category, $label, $message, $duration, $checkUrl);
        continue;
    }

    printFail($label, $message, $duration);
    addResult($stats, $category, 'fail', $duration);
    recordResult('fail', $category, $label, $message, $duration, $checkUrl);

    $failures[] = [
        'category' => $category,
        'label' => $label,
        'message' => $message,
        'duration' => $duration,
        'url' => $checkUrl,
    ];
}

/*
|--------------------------------------------------------------------------
| FAILS TRIÉS
|--------------------------------------------------------------------------
*/

if (!empty($failures))
{
    usort($failures, static function (array $a, array $b): int
    {
        return ((float) $b['duration']) <=> ((float) $a['duration']);
    });

    sectionTitle('FAILS TRIÉS');

    foreach ($failures as $failure)
    {
        printFail(
            (string) $failure['category'] . ' :: ' . (string) $failure['label'],
            (string) $failure['message'],
            (float) $failure['duration']
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
    $line = str_pad((string) $category, 22);
    $line .= ' ';
    $line .= color('OK: ' . (int) ($categoryStats['success'] ?? 0), C_GREEN . C_BOLD);
    $line .= '   ';
    $line .= color('FAIL: ' . (int) ($categoryStats['fail'] ?? 0), C_RED . C_BOLD);
    $line .= '   ';
    $line .= color('WARN: ' . (int) ($categoryStats['warn'] ?? 0), C_YELLOW . C_BOLD);
    $line .= '   ';
    $line .= color('TOTAL: ' . (int) ($categoryStats['total'] ?? 0), C_CYAN . C_BOLD);
    $line .= '   ';
    $line .= color('TIME: ' . formatDuration((float) ($categoryStats['duration'] ?? 0.0)), C_DIM);

    outLine($line);
}

/*
|--------------------------------------------------------------------------
| RÉSUMÉ FINAL
|--------------------------------------------------------------------------
*/

sectionTitle('RÉSUMÉ FINAL');

$g = $stats['global'];
$totalDuration = microtime(true) - $globalStart;

outLine(color(line('─'), C_DIM));
outLine(color('Tests exécutés : ', C_BOLD) . color((string) ((int) ($g['total'] ?? 0)), C_CYAN . C_BOLD));
outLine(color('Succès         : ', C_BOLD) . color((string) ((int) ($g['success'] ?? 0)), C_GREEN . C_BOLD));
outLine(color('Échecs         : ', C_BOLD) . color((string) ((int) ($g['fail'] ?? 0)), C_RED . C_BOLD));
outLine(color('Warnings       : ', C_BOLD) . color((string) ((int) ($g['warn'] ?? 0)), C_YELLOW . C_BOLD));
outLine(color('Temps cumulé   : ', C_BOLD) . color(formatDuration((float) ($g['duration'] ?? 0.0)), C_DIM));
outLine(color('Temps global   : ', C_BOLD) . color(formatDuration($totalDuration), C_DIM));
outLine(color(line('─'), C_DIM));

if (((int) ($g['fail'] ?? 0)) === 0)
{
    outLine(color('🏆 SUITE VALIDÉE', C_BG_GREEN . C_WHITE . C_BOLD));
}
else
{
    outLine(color('💥 SUITE À CORRIGER', C_BG_RED . C_WHITE . C_BOLD));
}

if (((int) ($g['fail'] ?? 0)) === 0 && ((int) ($g['warn'] ?? 0)) === 0)
{
    outLine(color('État global : nickel, tout est vert.', C_GREEN . C_BOLD));
}
elseif (((int) ($g['fail'] ?? 0)) === 0)
{
    outLine(color('État global : stable, avec quelques tests skippés.', C_YELLOW . C_BOLD));
}
else
{
    outLine(color('État global : il reste des points à corriger.', C_RED . C_BOLD));
}

/*
|--------------------------------------------------------------------------
| EXPORT
|--------------------------------------------------------------------------
*/

if ($exportEnabled)
{
    ensureDirectory($exportDirectory);

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