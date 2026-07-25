<?php

declare(strict_types=1);

use App\Controllers\Manga\ArtbookAjaxController;
use App\Controllers\Manga\ArtbookController;
use App\Controllers\Manga\MangaAjaxController;
use App\Controllers\Manga\MangaController;

use Framework\Http\Middleware\CsrfMiddleware;
use Framework\Http\Middleware\ExpectJsonMiddleware;
use Framework\Routing\Router;

/** @var Router $router */

$router->prefix('manga')->group(function (Router $router): void
{
    // =========================================
    // INDEX
    // =========================================

    $router->get(
        '',
        [MangaController::class, 'index']
    );

    $router->get(
        'lien',
        [MangaController::class, 'links']
    );


    // =========================================
    // AJOUT
    // =========================================

    $router->get(
        'ajouter',
        [MangaController::class, 'ajouter']
    );

    $router->get(
        'ajouter/manga',
        [MangaController::class, 'create']
    );

    $router->post(
        'ajouter/manga',
        [MangaController::class, 'store'],
        [CsrfMiddleware::class]
    );

    $router->get(
        'ajouter/artbook',
        [ArtbookController::class, 'create']
    );

    $router->post(
        'ajouter/artbook',
        [ArtbookController::class, 'store'],
        [CsrfMiddleware::class]
    );


    // =========================================
    // ARTBOOKS
    // =========================================

    $router->prefix('artbooks')->group(function (Router $router): void
    {
        $router->get(
            '',
            [ArtbookController::class, 'index']
        );

        $router->get(
            'page/{page:int}',
            [ArtbookController::class, 'index']
        );


        $router->get(
            '{slug}/modifier/{numero:int}',
            [ArtbookController::class, 'edit']
        );

        $router->post(
            '{slug}/modifier/{numero:int}',
            [ArtbookController::class, 'update'],
            [CsrfMiddleware::class]
        );

        $router->post(
            '{slug}/supprimer/{numero:int}',
            [ArtbookAjaxController::class, 'delete'],
            [
                ExpectJsonMiddleware::class,
                CsrfMiddleware::class,
            ]
        );


        $router->get(
            '{slug}/{numero:int}',
            [ArtbookController::class, 'show']
        );
    });


    // =========================================
    // SERIES
    // =========================================

    $router->prefix('series')->group(function (Router $router): void
    {
        $router->get(
            '',
            [MangaController::class, 'series']
        );

        $router->get(
            'page/{page:int}',
            [MangaController::class, 'series']
        );

        $router->get(
            'notes',
            [MangaController::class, 'notes']
        );

        $router->get(
            'a-lire',
            [MangaController::class, 'aLire']
        );


        $router->get(
            '{slug}/modifier/{numero:int}',
            [MangaController::class, 'edit']
        );

        $router->post(
            '{slug}/modifier/{numero:int}',
            [MangaController::class, 'update'],
            [CsrfMiddleware::class]
        );

        $router->post(
            '{slug}/supprimer/{numero:int}',
            [MangaAjaxController::class, 'delete'],
            [
                ExpectJsonMiddleware::class,
                CsrfMiddleware::class,
            ]
        );


        $router->get(
            '{slug}/{numero:int}',
            [MangaController::class, 'showManga']
        );

        $router->get(
            '{slug}',
            [MangaController::class, 'showSeries']
        );
    });


    // =========================================
    // AJAX HTML
    // =========================================

    $router->prefix('ajax')->group(function (Router $router): void
    {
        $router->get(
            'series/page/{page:int}',
            [MangaAjaxController::class, 'seriesPage']
        );

        $router->get(
            'artbooks/page/{page:int}',
            [ArtbookAjaxController::class, 'page']
        );
    });


    // =========================================
    // AJAX JSON
    // =========================================

    $router->prefix('ajax')
        ->middleware(ExpectJsonMiddleware::class)
        ->group(function (Router $router): void
        {
            $router->get(
                'recherche/artbooks/{query}',
                [ArtbookAjaxController::class, 'search']
            );

            $router->get(
                'recherche/{query}',
                [MangaAjaxController::class, 'search']
            );
        });


    // =========================================
    // AJAX JSON + CSRF
    // =========================================

    $router->prefix('ajax')
        ->middleware([
            ExpectJsonMiddleware::class,
            CsrfMiddleware::class,
        ])
        ->group(function (Router $router): void
        {
            $router->post(
                'update-note/{slug}/{numero:int}',
                [MangaAjaxController::class, 'updateNote']
            );

            $router->post(
                'update-read-status/{slug}/{numero:int}',
                [MangaAjaxController::class, 'updateReadStatus']
            );

            $router->post(
                'artbook/update-read-status/{slug}/{numero:int}',
                [ArtbookAjaxController::class, 'updateReadStatus']
            );
        });
});