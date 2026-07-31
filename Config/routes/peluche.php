<?php

declare(strict_types=1);

use App\Controllers\Peluche\PelucheAjaxController;
use App\Controllers\Peluche\PelucheController;

use Framework\Http\Middleware\CsrfMiddleware;
use Framework\Http\Middleware\ExpectJsonMiddleware;
use Framework\Routing\Router;

/** @var Router $router */

$router->prefix('peluche')->group(function (Router $router): void
{
    // =========================================
    // INDEX
    // =========================================

    $router->get('', [PelucheController::class, 'index']);

    // =========================================
    // WAIFUS
    // =========================================

    $router->prefix('waifus')->group(function (Router $router): void
    {
        $router->get('', [PelucheController::class, 'waifus']);
        $router->get('page/{page:int}', [PelucheController::class, 'waifus']);

        // =========================================
        // MODIFICATION
        // =========================================

        $router->get(
            '{slug}/modifier/{numero:int}',
            [PelucheController::class, 'edit']
        );

        $router->post(
            '{slug}/modifier/{numero:int}',
            [PelucheController::class, 'update'],
            [CsrfMiddleware::class]
        );

        // =========================================
        // SUPPRESSION
        // =========================================

        $router->post(
            '{slug}/supprimer/{numero:int}',
            [PelucheAjaxController::class, 'delete'],
            [ExpectJsonMiddleware::class, CsrfMiddleware::class]
        );

        // =========================================
        // CONSULTATION
        // =========================================

        $router->get(
            '{slug}/{numero:int}',
            [PelucheController::class, 'showWaifu']
        );
    });

    // =========================================
    // AJOUT
    // =========================================

    $router->get('ajouter', [PelucheController::class, 'create']);

    $router->post(
        'ajouter',
        [PelucheController::class, 'store'],
        [CsrfMiddleware::class]
    );

    // =========================================
    // AJAX
    // =========================================

    $router->prefix('ajax')->group(function (Router $router): void
    {
        // =========================================
        // HTML
        // =========================================

        $router->get(
            'waifus/page/{page:int}',
            [PelucheAjaxController::class, 'waifusPage']
        );

        // =========================================
        // JSON
        // =========================================

        $router
            ->middleware(ExpectJsonMiddleware::class)
            ->group(function (Router $router): void
            {
                $router->get(
                    'recherche/{query}',
                    [PelucheAjaxController::class, 'search']
                );

                $router
                    ->middleware(CsrfMiddleware::class)
                    ->group(function (Router $router): void
                    {
                        $router->post(
                            'update-collect-status/{slug}/{numero:int}',
                            [PelucheAjaxController::class, 'updateCollectStatus']
                        );
                    });
            });
    });
});