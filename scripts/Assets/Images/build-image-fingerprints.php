<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli')
{ http_response_code(404); exit; }
require_once dirname(__DIR__, 3) . '/vendor/autoload.php';
require_once __DIR__ . '/../../Support/BuildLock.php';
BuildLock::acquire(dirname(__DIR__, 3));
echo 'Image fingerprints refreshed: ' . \App\Support\Media\ImageFingerprints::rebuild() . PHP_EOL;
