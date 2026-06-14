<?php

declare(strict_types=1);

use Framework\Application\Bootstrap;

if (! defined('ROOT'))
{
    define(
        'ROOT',
        dirname(__DIR__, 2),
    );
}

require ROOT . '/vendor/autoload.php';
require ROOT . '/Framework/Support/helpers.php';

Bootstrap::loadEnvOnly();

$_ENV['APP_ENV'] = 'testing';
$_SERVER['APP_ENV'] = 'testing';

putenv('APP_ENV=testing');

if (
    env('APP_ENV')
    !== 'testing'
)
{
    throw new RuntimeException(
        'HTTP tests must run in testing environment.',
    );
}