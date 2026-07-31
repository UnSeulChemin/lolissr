<?php

declare(strict_types=1);

use Framework\Application\Bootstrap;
use Framework\Config\Config;
use Framework\Config\Env;

use RuntimeException;

if (! defined('ROOT'))
{
    define('ROOT', dirname(__DIR__, 2));
}

require ROOT . '/vendor/autoload.php';
require ROOT . '/Framework/Support/Helpers.php';

// =========================================
// ENVIRONNEMENT
// =========================================

Bootstrap::loadEnvOnly();

Env::set('APP_ENV', 'testing');
Config::clear();

if (Env::get('APP_ENV') !== 'testing')
{
    throw new RuntimeException(
        'HTTP tests must run in testing environment.'
    );
}