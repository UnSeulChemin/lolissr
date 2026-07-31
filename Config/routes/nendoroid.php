<?php

declare(strict_types=1);

use App\Controllers\Nendoroid\NendoroidAjaxController;
use App\Controllers\Nendoroid\NendoroidController;

use Framework\Http\Middleware\CsrfMiddleware;
use Framework\Http\Middleware\ExpectJsonMiddleware;
use Framework\Routing\Router;

/** @var Router $router */

$router->prefix('nendoroid')->group(function (Router $router): void
{
    // =========================================
    // INDEX
    // =========================================

    $router->get('', [NendoroidController::class, 'index']);

    // =========================================
    // WAIFUS
    // =========================================

    $router->prefix('waifus')->group(function (Router $router): void
    {
        $router->get('', [NendoroidController::class, 'waifus']);
        $router->get('page/{page:int}', [NendoroidController::class, 'waifus']);

        // =========================================
        // MODIFICATION
        // =========================================

        $router->get(
            '{slug}/modifier/{numero:int}',
            [NendoroidController::class, 'edit']
        );

        $router->post(
            '{slug}/modifier/{numero:int}',
            [NendoroidController::class, 'update'],
            [CsrfMiddleware::class]
        );

        // =========================================
        // SUPPRESSION
        // =========================================

        $router->post(
            '{slug}/supprimer/{numero:int}',
            [NendoroidAjaxController::class, 'delete'],
            [ExpectJsonMiddleware::class, CsrfMiddleware::class]
        );

        // =========================================
        // CONSULTATION
        // =========================================

        $router->get(
            '{slug}/{numero:int}',
            [NendoroidController::class, 'showWaifu']
        );
    });

    // =========================================
    // AJOUT
    // =========================================

    $router->get('ajouter', [NendoroidController::class, 'create']);

    $router->post(
        'ajouter',
        [NendoroidController::class, 'store'],
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
            [NendoroidAjaxController::class, 'waifusPage']
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
                    [NendoroidAjaxController::class, 'search']
                );

                $router
                    ->middleware(CsrfMiddleware::class)
                    ->group(function (Router $router): void
                    {
                        $router->post(
                            'update-collect-status/{slug}/{numero:int}',
                            [NendoroidAjaxController::class, 'updateCollectStatus']
                        );
                    });
            });
    });
});