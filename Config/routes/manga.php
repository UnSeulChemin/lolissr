<?php

declare(strict_types=1);

use App\Controllers\Manga\MangaAjaxController;
use App\Controllers\Manga\MangaController;

use Framework\Http\Middleware\CsrfMiddleware;
use Framework\Http\Middleware\ExpectJsonMiddleware;
use Framework\Routing\Router;

/** @var Router $router */

$router->prefix('manga')->group(function (Router $router): void
{
    $router->get('', [MangaController::class, 'index']);

    $router->get('lien', [MangaController::class, 'links']);

    /*
    |--------------------------------------------------------------------------
    | AJOUT
    |--------------------------------------------------------------------------
    */

    $router->get('ajouter', [MangaController::class, 'ajouter']);

    $router->get('ajouter/manga', [MangaController::class, 'create']);

    $router->post('ajouter/manga', [MangaController::class, 'store'], [CsrfMiddleware::class]);

    $router->get('ajouter/artbook', [MangaController::class, 'createArtbook']);

    $router->post('ajouter/artbook', [MangaController::class, 'storeArtbook'], [CsrfMiddleware::class]);

    /*
    |--------------------------------------------------------------------------
    | ARTBOOKS
    |--------------------------------------------------------------------------
    */

    $router->prefix('artbooks')->group(function (Router $router): void
    {
        $router->get('', [MangaController::class, 'artbooks']);

        $router->get('page/{page:int}', [MangaController::class, 'artbooks']);

        /*
        |--------------------------------------------------------------------------
        | ACTIONS SUR UN ARTBOOK
        |--------------------------------------------------------------------------
        */

        $router->get('{slug}/modifier/{numero:int}', [MangaController::class, 'editArtbook']);

        $router->post('{slug}/modifier/{numero:int}',
            [MangaController::class, 'updateArtbook'],
            [CsrfMiddleware::class]
        );

        $router->post('{slug}/supprimer/{numero:int}',
            [MangaAjaxController::class, 'deleteArtbook'],
            [ExpectJsonMiddleware::class, CsrfMiddleware::class]
        );

        /*
        |--------------------------------------------------------------------------
        | CONSULTATION
        |--------------------------------------------------------------------------
        */

        $router->get('{slug}/{numero:int}', [MangaController::class, 'showArtbook']);
    });

    /*
    |--------------------------------------------------------------------------
    | SERIES
    |--------------------------------------------------------------------------
    */

    $router->prefix('series')->group(function (Router $router): void
    {
        $router->get('', [MangaController::class, 'series']);

        $router->get('page/{page:int}', [MangaController::class, 'series']);

        $router->get('notes', [MangaController::class, 'notes']);

        $router->get('a-lire', [MangaController::class, 'aLire']);

        /*
        |--------------------------------------------------------------------------
        | ACTIONS SUR UN TOME
        |--------------------------------------------------------------------------
        */

        $router->get('{slug}/modifier/{numero:int}', [MangaController::class, 'edit']);

        $router->post('{slug}/modifier/{numero:int}',
            [MangaController::class, 'update'],
            [CsrfMiddleware::class]
        );

        $router->post('{slug}/supprimer/{numero:int}',
            [MangaAjaxController::class, 'delete'],
            [ExpectJsonMiddleware::class, CsrfMiddleware::class]
        );

        /*
        |--------------------------------------------------------------------------
        | CONSULTATION
        |--------------------------------------------------------------------------
        */

        $router->get('{slug}/{numero:int}', [MangaController::class, 'showManga']);

        $router->get('{slug}', [MangaController::class, 'showSeries']);
    });

    /*
    |--------------------------------------------------------------------------
    | AJAX HTML
    |--------------------------------------------------------------------------
    */

    $router->prefix('ajax')->group(function (Router $router): void
    {
        $router->get('series/page/{page:int}', [MangaAjaxController::class, 'seriesPage']);

        $router->get('artbooks/page/{page:int}', [MangaAjaxController::class, 'artbooksPage']);
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
            $router->get('recherche/artbooks/{query}', [MangaAjaxController::class, 'searchArtbooks']);

            $router->get('recherche/{query}', [MangaAjaxController::class, 'search']);
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
            $router->post('update-note/{slug}/{numero:int}', [MangaAjaxController::class, 'updateNote']);

            $router->post('update-read-status/{slug}/{numero:int}', [MangaAjaxController::class, 'updateReadStatus']);

            $router->post('artbook/update-read-status/{slug}/{numero:int}', [MangaAjaxController::class, 'updateArtbookReadStatus']);
        });
});