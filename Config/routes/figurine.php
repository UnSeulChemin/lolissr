<?php

declare(strict_types=1);

use App\Controllers\Figurine\FigurineAjaxController;
use App\Controllers\Figurine\FigurineController;

use Framework\Http\Middleware\CsrfMiddleware;
use Framework\Http\Middleware\ExpectJsonMiddleware;
use Framework\Routing\Router;

/** @var Router $router */

$router->prefix('figurine')->group(function (Router $router): void
{
    $router->get('', [FigurineController::class, 'index']);

    $router->get('lien', [FigurineController::class, 'links']);

    /*
    |--------------------------------------------------------------------------
    | WAIFUS
    |--------------------------------------------------------------------------
    */

    $router->prefix('waifus')->group(function (Router $router): void
    {
        $router->get('', [FigurineController::class, 'waifus']);

        $router->get('page/{page:int}', [FigurineController::class, 'waifus']);

        /*
        |--------------------------------------------------------------------------
        | ACTIONS SUR UNE FIGURINE
        |--------------------------------------------------------------------------
        */

        $router->get('{slug}/modifier/{numero:int}', [FigurineController::class, 'edit']);

        $router->post('{slug}/modifier/{numero:int}',
            [FigurineController::class, 'update'],
            [CsrfMiddleware::class]
        );

        $router->post('{slug}/supprimer/{numero:int}',
            [FigurineAjaxController::class, 'delete'],
            [ExpectJsonMiddleware::class, CsrfMiddleware::class]
        );

        /*
        |--------------------------------------------------------------------------
        | CONSULTATION
        |--------------------------------------------------------------------------
        */

        $router->get('{slug}/{numero:int}', [FigurineController::class, 'showWaifu']);
    });

    /*
    |--------------------------------------------------------------------------
    | AJOUT
    |--------------------------------------------------------------------------
    */

    $router->get('ajouter', [FigurineController::class, 'create']);

    $router->post('ajouter', [FigurineController::class, 'store'], [CsrfMiddleware::class]);

    /*
    |--------------------------------------------------------------------------
    | AJAX HTML
    |--------------------------------------------------------------------------
    */

    $router->prefix('ajax')->group(function (Router $router): void
    {
        $router->get('waifus/page/{page:int}', [FigurineAjaxController::class, 'waifusPage']);
    });

    /*
    |--------------------------------------------------------------------------
    | AJAX JSON
    |--------------------------------------------------------------------------
    */

    $router->prefix('ajax')
        ->middleware(ExpectJsonMiddleware::class)
        ->group(function (Router $router): void
        {
            $router->get('recherche/{query}', [FigurineAjaxController::class, 'search']);
        });

    /*
    |--------------------------------------------------------------------------
    | AJAX JSON + CSRF
    |--------------------------------------------------------------------------
    */

    $router->prefix('ajax')
        ->middleware([ExpectJsonMiddleware::class, CsrfMiddleware::class])
        ->group(function (Router $router): void
        {
            $router->post('update-collect-status/{slug}/{numero:int}', [FigurineAjaxController::class, 'updateCollectStatus']);
        });
});