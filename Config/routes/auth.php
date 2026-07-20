<?php

declare(strict_types=1);

use App\Controllers\Auth\AuthController;

use Framework\Application\App;
use Framework\Http\Middleware\CsrfMiddleware;
use Framework\Http\Middleware\GuestMiddleware;
use Framework\Routing\Router;

/** @var Router $router */

$router->get('connexion', [AuthController::class, 'login'], [GuestMiddleware::class]);

$router->post('connexion',
    [AuthController::class, 'authenticate'],
    [GuestMiddleware::class, CsrfMiddleware::class]
);

if (! App::isProduction())
{
    $router->get('inscription', [AuthController::class, 'register'], [GuestMiddleware::class]);

    $router->post('inscription',
        [AuthController::class, 'store'],
        [GuestMiddleware::class, CsrfMiddleware::class]
    );
}