<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

require __DIR__ . '/Support/Assertions.php';
require __DIR__ . '/Support/HttpClient.php';
require __DIR__ . '/Support/HtmlReport.php';
require __DIR__ . '/Support/Stats.php';

$config =
    require __DIR__ . '/http-config.php';

http_login();

$tests = [];

foreach (
    glob(
        __DIR__ . '/Cases/*.php',
    ) ?: []
    as $file
) {
    require $file;
}

return [

    'base' => (string) $config['base'],

    'tests' => $tests,

];