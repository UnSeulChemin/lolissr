<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

require __DIR__ . '/Support/Assertions.php';
require __DIR__ . '/Support/HtmlReport.php';
require __DIR__ . '/Support/HttpClient.php';
require __DIR__ . '/Support/HttpTestRunner.php';
require __DIR__ . '/Support/ReportSanitizer.php';
require __DIR__ . '/Support/Stats.php';

// =========================================
// AUTHENTIFICATION
// =========================================

http_login();

// =========================================
// CAS DE TEST
// =========================================

/** @var list<array<string, mixed>> $tests */
$tests = [];

$caseFiles = glob(__DIR__ . '/Cases/*.php');

if ($caseFiles !== false)
{
    sort($caseFiles);

    foreach ($caseFiles as $file)
    {
        require $file;
    }
}

// =========================================
// BOOTSTRAP
// =========================================

return [
    'base' => http_base(),
    'tests' => $tests
];