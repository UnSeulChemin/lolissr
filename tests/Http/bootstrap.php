<?php

declare(strict_types=1);

if (!defined('ROOT'))
{
    define(
        'ROOT',
        dirname(__DIR__, 2),
    );
}

require ROOT . '/vendor/autoload.php';
require ROOT . '/Framework/Support/helpers.php';

$_ENV['APP_ENV'] = 'testing';
$_SERVER['APP_ENV'] = 'testing';

putenv('APP_ENV=testing');

if (
    ($_ENV['APP_ENV'] ?? '')
    !== 'testing'
)
{
    throw new RuntimeException(
        'HTTP tests must run in testing environment.',
    );
}