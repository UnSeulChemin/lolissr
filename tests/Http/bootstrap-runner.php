<?php

declare(strict_types=1);

define(
    'ROOT',
    dirname(__DIR__, 2),
);

require ROOT . '/vendor/autoload.php';
require ROOT . '/Framework/Support/helpers.php';

require __DIR__ . '/Support/HttpClient.php';
require __DIR__ . '/Support/Assertions.php';
require __DIR__ . '/Support/Terminal.php';

$config = require __DIR__ . '/config.php';

$base = $config['base'];

$tests = [];

foreach (glob(__DIR__ . '/cases/safe/*.php') as $file)
{
    require $file;
}

return [
    'base' => $base,
    'tests' => $tests,
];