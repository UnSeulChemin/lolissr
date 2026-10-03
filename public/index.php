<?php

declare(strict_types=1);

use App\Http\Controllers\ErrorController;
use App\Providers\ServiceProvider;

use Framework\Application\Bootstrap;

if (! defined('ROOT'))
{
    define('ROOT', dirname(__DIR__));
}

require ROOT . '/vendor/autoload.php';
require ROOT . '/Framework/Support/Helpers.php';
require ROOT . '/App/Support/Helpers.php';

Bootstrap::run([ErrorController::class, 'handle'], [ServiceProvider::class, 'register']);