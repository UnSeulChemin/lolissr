<?php

declare(strict_types=1);

require dirname(__DIR__) . '/scripts/lib/CssBundleBuilder.php';

$directory = dirname(__DIR__) . '/public/css';
$bundle = CssBundleBuilder::compile($directory);
if (! is_file($directory . '/app.bundle.css') || file_get_contents($directory . '/app.bundle.css') !== $bundle)
{
    throw new RuntimeException('CSS bundle is stale: run php scripts/build-css.php.');
}
if (substr_count($bundle, '@import') !== 1 || ! str_contains($bundle, 'https://fonts.googleapis.com/'))
{
    throw new RuntimeException('Expected only the external font import in the bundle.');
}
$sample = '/* comment */ .a .b { content: "a  b /* literal */"; width: calc(100% - 2px); --label: "hello  world"; }';
$expected = '.a .b { content: "a  b /* literal */"; width: calc(100% - 2px); --label: "hello  world"; }';
if (CssBundleBuilder::compact($sample) !== $expected)
{
    throw new RuntimeException('CSS compaction changed significant whitespace or quoted content.');
}
echo "PASS: current CSS bundle, local imports expanded, quoted content and calc() preserved.\n";
