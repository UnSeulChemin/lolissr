<?php

declare(strict_types=1);

if (!defined('ROOT'))
{
    define('ROOT', dirname(__DIR__, 2));
}

require_once ROOT . '/vendor/autoload.php';
require_once ROOT . '/Framework/Support/helpers.php';

require_once __DIR__ . '/Support/Environment.php';
require_once __DIR__ . '/Support/Terminal.php';
require_once __DIR__ . '/Support/Filesystem.php';
require_once __DIR__ . '/Support/Stats.php';
require_once __DIR__ . '/Support/HttpClient.php';
require_once __DIR__ . '/Support/Assertions.php';
require_once __DIR__ . '/Support/Exporter.php';

loadTestEnvironment();

$config = require __DIR__ . '/config.php';

$base = (string) $config['base'];

$realSlug = (string) $config['realSlug'];
$realNumero = (int) $config['realNumero'];
$nonCanonicalSlug = (string) $config['nonCanonicalSlug'];

$casesDirectory = (string) $config['casesDirectory'];
$fixturesDirectory = (string) $config['fixturesDirectory'];
$tmpUploadsDirectory = (string) $config['tmpUploadsDirectory'];
$exportDirectory = (string) $config['exportDirectory'];

$testAjaxUpdate = (bool) ($config['testAjaxUpdate'] ?? false);
$testCanonicalRedirect = (bool) ($config['testCanonicalRedirect'] ?? false);
$testPostAjouter = (bool) ($config['testPostAjouter'] ?? false);
$testPostUpdate = (bool) ($config['testPostUpdate'] ?? false);
$testUploadDuplicateSlugNumero = (bool) ($config['testUploadDuplicateSlugNumero'] ?? false);
$testUploadInvalidImage = (bool) ($config['testUploadInvalidImage'] ?? false);
$testUploadMaxSize = (bool) ($config['testUploadMaxSize'] ?? false);

$exportEnabled = (bool) ($config['exportEnabled'] ?? false);

ensureDirectory($exportDirectory);
ensureDirectory($tmpUploadsDirectory);
cleanTmpUploads($tmpUploadsDirectory);

guardTestingEnvironment(
    $testPostAjouter,
    $testPostUpdate,
);

$plainOutput = '';
$allResults = [];

$tests = [];
$htmlChecks = [];
$postChecks = [];

$stats = createEmptyStats();

$failures = [];
$warnings = [];