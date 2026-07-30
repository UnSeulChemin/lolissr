<?php

declare(strict_types=1);

use App\Controllers\ErrorController;
use App\Providers\ServiceProvider;

use Framework\Application\Bootstrap;

if (! defined('ROOT'))
{
    define('ROOT', dirname(__DIR__));
}

require_once ROOT . '/vendor/autoload.php';
require_once ROOT . '/Framework/Support/Helpers.php';
require_once ROOT . '/App/Support/Helpers.php';

Bootstrap::run(
    [ErrorController::class, 'handle'],
    [ServiceProvider::class, 'register']
);