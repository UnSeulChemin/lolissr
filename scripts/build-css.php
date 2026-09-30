<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/CssBundleBuilder.php';

$cssDirectory = dirname(__DIR__) . '/public/css';
$cssBundle = CssBundleBuilder::compile($cssDirectory);
$cssOutput = $cssDirectory . '/app.bundle.css';
if (! is_file($cssOutput) || file_get_contents($cssOutput) !== $cssBundle)
{
    if (file_put_contents($cssOutput, $cssBundle, LOCK_EX) === false)
    {
        throw new RuntimeException('Cannot write CSS bundle.');
    }
}
echo 'CSS bundle: ' . strlen($cssBundle) . ' bytes' . PHP_EOL;
