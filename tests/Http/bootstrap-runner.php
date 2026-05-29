<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

require __DIR__ . '/Support/HttpClient.php';
require __DIR__ . '/Support/Assertions.php';
require __DIR__ . '/Support/Terminal.php';
require __DIR__ . '/Support/Stats.php';
require __DIR__ . '/Support/HtmlReport.php';

$config =
    require __DIR__ . '/config.php';

$tests = [];

$files = glob(
    __DIR__ . '/cases/safe/*.php',
) ?: [];

sort($files);

foreach ($files as $file)
{
    require $file;
}

return [

    'config' => $config,

    'base' => (string) $config['base'],

    'tests' => $tests,

];