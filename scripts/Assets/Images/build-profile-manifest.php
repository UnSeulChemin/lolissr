<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli')
{ http_response_code(404); exit; }
require_once __DIR__ . '/../../Support/BuildLock.php';
BuildLock::acquire(dirname(__DIR__, 3));
require_once dirname(__DIR__, 3) . '/vendor/autoload.php';
echo 'Profile image version: ' . \App\Support\Media\ImageAssets::rebuildProfileVersion() . PHP_EOL;
require_once __DIR__ . '/build-image-fingerprints.php';
