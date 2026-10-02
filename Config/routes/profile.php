<?php

declare(strict_types=1);

use App\Http\Controllers\Profile\ProfileAjaxController;
use App\Http\Controllers\Profile\ProfileController;

use Framework\Http\Middleware\CsrfMiddleware;
use Framework\Http\Middleware\ExpectJsonMiddleware;
use Framework\Routing\Router;

/** @var Router $router */

$router->prefix('profil')->group(function (Router $router): void
{
    // =========================================
    // PAGES
    // =========================================

    $router->get('', [ProfileController::class, 'index']);
    $router->get('personnalisation', [ProfileController::class, 'customization']);
    $router->get('succes', [ProfileController::class, 'achievements']);
    $router->get('xp', [ProfileController::class, 'xp']);

    // =========================================
    // AJAX
    // =========================================

    $router
        ->prefix('ajax')
        ->middleware(ExpectJsonMiddleware::class)
        ->group(function (Router $router): void
        {
            $router->get('titles', [ProfileAjaxController::class, 'titles']);
            $router->get('avatars', [ProfileAjaxController::class, 'avatars']);
            $router->get('banners', [ProfileAjaxController::class, 'banners']);
            $router->get('frames', [ProfileAjaxController::class, 'frames']);

            $router
                ->middleware(CsrfMiddleware::class)
                ->group(function (Router $router): void
                {
                    $router->post('update-title', [ProfileAjaxController::class, 'updateTitle']);
                    $router->post('update-avatar', [ProfileAjaxController::class, 'updateAvatar']);
                    $router->post('update-banner', [ProfileAjaxController::class, 'updateBanner']);
                    $router->post('update-frame', [ProfileAjaxController::class, 'updateFrame']);
                });
        });
});
