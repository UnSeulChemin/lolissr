<?php

declare(strict_types=1);

use Framework\Application\Bootstrap;
use Framework\Config\Config;
use Framework\Config\Env;

if (! defined('ROOT'))
{
    define('ROOT', dirname(__DIR__, 2));
}

require ROOT . '/vendor/autoload.php';
require ROOT . '/Framework/Support/helpers.php';

Bootstrap::loadEnvOnly();

Env::set('APP_ENV', 'testing');
Config::clear();

if (env('APP_ENV') !== 'testing')
{
    throw new RuntimeException(
        'HTTP tests must run in testing environment.'
    );
}