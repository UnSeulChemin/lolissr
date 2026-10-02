<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\AuthController;

use Framework\Config\ApplicationConfig;
use Framework\Http\Middleware\CsrfMiddleware;
use Framework\Http\Middleware\GuestMiddleware;
use Framework\Routing\Router;

/** @var Router $router */

// =========================================
// CONNEXION
// =========================================

$router->get(
    'connexion',
    [AuthController::class, 'login'],
    [GuestMiddleware::class]
);

$router->post(
    'connexion',
    [AuthController::class, 'authenticate'],
    [GuestMiddleware::class, CsrfMiddleware::class]
);

// =========================================
// INSCRIPTION
// =========================================

if (! ApplicationConfig::isProduction() && env_bool('REGISTRATION_ENABLED', false))
{
    $router->get(
        'inscription',
        [AuthController::class, 'register'],
        [GuestMiddleware::class]
    );

    $router->post(
        'inscription',
        [AuthController::class, 'store'],
        [GuestMiddleware::class, CsrfMiddleware::class]
    );
}