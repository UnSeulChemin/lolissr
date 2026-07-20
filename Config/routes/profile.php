<?php

declare(strict_types=1);

use App\Controllers\ProfileAjaxController;
use App\Controllers\ProfileController;

use Framework\Http\Middleware\CsrfMiddleware;
use Framework\Http\Middleware\ExpectJsonMiddleware;
use Framework\Routing\Router;

/** @var Router $router */

$router->get('profil', [ProfileController::class, 'index']);

$router->get('profil/personnalisation', [ProfileController::class, 'customization']);

/*
|--------------------------------------------------------------------------
| AJAX JSON
|--------------------------------------------------------------------------
*/

$router->prefix('profil/ajax')
    ->middleware(ExpectJsonMiddleware::class)
    ->group(function (Router $router): void
{
    $router->get('titles', [ProfileAjaxController::class, 'titles']);

    $router->get('avatars', [ProfileAjaxController::class, 'avatars']);

    $router->get('banners', [ProfileAjaxController::class, 'banners']);

    $router->get('frames', [ProfileAjaxController::class, 'frames']);
});

/*
|--------------------------------------------------------------------------
| AJAX JSON + CSRF
|--------------------------------------------------------------------------
*/

$router->prefix('profil/ajax')
    ->middleware([ExpectJsonMiddleware::class, CsrfMiddleware::class])
    ->group(function (Router $router): void
{
    $router->post('update-title', [ProfileAjaxController::class, 'updateTitle']);

    $router->post('update-avatar', [ProfileAjaxController::class, 'updateAvatar']);

    $router->post('update-banner', [ProfileAjaxController::class, 'updateBanner']);

    $router->post('update-frame', [ProfileAjaxController::class, 'updateFrame']);
});