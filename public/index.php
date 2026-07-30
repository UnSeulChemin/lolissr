<?php

declare(strict_types=1);

use App\Controllers\ErrorController;
use App\Providers\AppServiceProvider;

use Framework\Application\Bootstrap;

if (! defined('ROOT'))
{
    define('ROOT', dirname(__DIR__));
}

require_once ROOT . '/vendor/autoload.php';
require_once ROOT . '/Framework/Support/helpers.php';
require_once ROOT . '/App/Support/helpers.php';

Bootstrap::run(
    [ErrorController::class, 'handle'],
    [AppServiceProvider::class, 'register']
);