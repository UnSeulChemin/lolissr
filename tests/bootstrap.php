<?php

declare(strict_types=1);

use RuntimeException;

if (!defined('ROOT'))
{
    define(
        'ROOT',
        dirname(__DIR__),
    );
}

require_once ROOT . '/vendor/autoload.php';
require_once ROOT . '/Framework/Support/helpers.php';

/*
|--------------------------------------------------------------------------
| TEST ENVIRONMENT
|--------------------------------------------------------------------------
*/

$_ENV['APP_ENV'] = 'testing';
$_SERVER['APP_ENV'] = 'testing';

putenv('APP_ENV=testing');

/*
|--------------------------------------------------------------------------
| SAFE MODE
|--------------------------------------------------------------------------
*/

foreach ([
    'TESTS_ENABLED' => 'true',

    'TEST_UPLOAD_MODE' => 'true',
    'TEST_UPLOAD_REAL' => 'false',

    'TEST_POST_AJOUTER' => 'false',
    'TEST_POST_UPDATE' => 'false',

    'TEST_AJAX_UPDATE' => 'false',

    'TEST_UPLOAD_DUPLICATE_SLUG_NUMERO' => 'false',
    'TEST_UPLOAD_INVALID_IMAGE' => 'false',
    'TEST_UPLOAD_MAX_SIZE' => 'false',
] as $key => $value)
{
    $_ENV[$key] = $value;
    $_SERVER[$key] = $value;

    putenv(
        "{$key}={$value}",
    );
}

/*
|--------------------------------------------------------------------------
| SAFETY CHECK
|--------------------------------------------------------------------------
*/

if (
    ($_ENV['APP_ENV'] ?? '')
    !== 'testing'
)
{
    throw new RuntimeException(
        'Tests must run in testing environment.',
    );
}