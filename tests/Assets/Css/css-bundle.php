<?php

declare(strict_types=1);

require dirname(__DIR__, 3) . '/scripts/Assets/Css/Support/CssBundleBuilder.php';

$directory = dirname(__DIR__, 3) . '/public/css';
$bundle = CssBundleBuilder::compile($directory);
if (! is_file($directory . '/app.bundle.css') || file_get_contents($directory . '/app.bundle.css') !== $bundle)
{
    throw new RuntimeException('CSS bundle is stale: run php scripts/Assets/Css/build-css.php.');
}
if (str_contains($bundle, '@import'))
{
    throw new RuntimeException('CSS bundle must not delay fonts through an import.');
}
$sample = '/* comment */ .a .b { content: "a  b /* literal */"; width: calc(100% - 2px); --label: "hello  world"; }';
$expected = '.a .b { content: "a  b /* literal */"; width: calc(100% - 2px); --label: "hello  world"; }';
if (CssBundleBuilder::compact($sample) !== $expected)
{
    throw new RuntimeException('CSS compaction changed significant whitespace or quoted content.');
}
echo "PASS: current CSS bundle, local imports expanded, quoted content and calc() preserved.\n";
