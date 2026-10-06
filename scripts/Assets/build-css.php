<?php

declare(strict_types=1);

require_once __DIR__ . '/../Support/CssBundleBuilder.php';
require_once __DIR__ . '/../Support/BuildLock.php';
require_once __DIR__ . '/../Support/AtomicFile.php';
BuildLock::acquire(dirname(__DIR__, 2));

$cssDirectory = dirname(__DIR__, 2) . '/public/css';
$cssBundle = CssBundleBuilder::compile($cssDirectory);
$cssOutput = $cssDirectory . '/app.bundle.css';
AtomicFile::writeIfChanged($cssOutput, $cssBundle);
echo 'CSS bundle: ' . strlen($cssBundle) . ' bytes' . PHP_EOL;
