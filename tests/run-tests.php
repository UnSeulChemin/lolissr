<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

/*
|--------------------------------------------------------------------------
| CHARGEMENT AUTOMATIQUE DES CASES
|--------------------------------------------------------------------------
*/

$caseFiles = glob(__DIR__ . '/cases/*.php') ?: [];
sort($caseFiles);

foreach ($caseFiles as $caseFile)
{
    require $caseFile;
}

/*
|--------------------------------------------------------------------------
| RUNNER GET
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

        recordResult('success', $category, $test['label'] . ' -> ' . $test['path'], $result['message'], $duration, $testUrl);
    }
    else
    {
        printFail($test['label'] . ' -> ' . $test['path'], $result['message'], $duration);
        addResult($stats, $category, 'fail', $duration);

        recordResult('fail', $category, $test['label'] . ' -> ' . $test['path'], $result['message'], $duration, $testUrl);

        $failures[] = [
            'category' => $category,
            'label' => $test['label'],
            'message' => $result['message'],
            'duration' => $duration,
            'url' => $testUrl,
        ];
    }
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
    $category = $check['category'];

    if ($currentCategory !== $category)
    {
        $currentCategory = $category;
        categoryTitle($category);
    }

    $result = isset($check['callback']) && is_callable($check['callback'])
        ? runCallableCheck($check['callback'])
        : ['ok' => false, 'message' => 'callback absente', 'duration' => 0.0];

    $duration = $result['duration'] ?? 0.0;
    $ok = (bool) ($result['ok'] ?? false);
    $message = (string) ($result['message'] ?? '');
    $checkUrl = $check['url'] ?? null;

    if ($ok)
    {
        printOk($check['label'], $message, $duration);
        addResult($stats, $category, 'success', $duration);

        recordResult('success', $category, $check['label'], $message, $duration, $checkUrl);
    }
    else
    {
        printFail($check['label'], $message, $duration);
        addResult($stats, $category, 'fail', $duration);

        recordResult('fail', $category, $check['label'], $message, $duration, $checkUrl);

        $failures[] = [
            'category' => $category,
            'label' => $check['label'],
            'message' => $message,
            'duration' => $duration,
            'url' => $checkUrl,
        ];
    }
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
    $category = $check['category'];

    if ($currentCategory !== $category)
    {
        $currentCategory = $category;
        categoryTitle($category);
    }

    $result = isset($check['callback']) && is_callable($check['callback'])
        ? runCallableCheck($check['callback'])
        : ['ok' => false, 'warn' => false, 'message' => 'callback absente', 'duration' => 0.0];

    $duration = $result['duration'] ?? 0.0;
    $ok = (bool) ($result['ok'] ?? false);
    $warn = (bool) ($result['warn'] ?? false);
    $message = (string) ($result['message'] ?? '');
    $checkUrl = $check['url'] ?? null;

    if ($warn)
    {
        printWarn($check['label'], $message, $duration);
        addResult($stats, $category, 'warn', $duration);

        recordResult('warn', $category, $check['label'], $message, $duration, $checkUrl);

        $warnings[] = [
            'category' => $category,
            'label' => $check['label'],
            'message' => $message,
            'duration' => $duration,
            'url' => $checkUrl,
        ];

        continue;
    }

    if ($ok)
    {
        printOk($check['label'], $message, $duration);
        addResult($stats, $category, 'success', $duration);

        recordResult('success', $category, $check['label'], $message, $duration, $checkUrl);
    }
    else
    {
        printFail($check['label'], $message, $duration);
        addResult($stats, $category, 'fail', $duration);

        recordResult('fail', $category, $check['label'], $message, $duration, $checkUrl);

        $failures[] = [
            'category' => $category,
            'label' => $check['label'],
            'message' => $message,
            'duration' => $duration,
            'url' => $checkUrl,
        ];
    }
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
| RÉSUMÉ CATÉGORIES
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
| RÉSUMÉ FINAL
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
| EXPORT
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