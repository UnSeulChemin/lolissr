<?php

declare(strict_types=1);

use App\Controllers\MainController;

use App\Controllers\Manga\MangaAjaxController;
use App\Controllers\Manga\MangaController;

use App\Controllers\Chinois\ChinoisAjaxController;
use App\Controllers\Chinois\ChinoisController;

use App\Core\Http\Middleware\AjaxOnlyMiddleware;
use App\Core\Http\Middleware\CsrfMiddleware;
use App\Core\Http\Middleware\PostOnlyMiddleware;

use App\Core\Http\Router;

return static function (Router $router): void {

    /*
    |--------------------------------------------------------------------------
    | Main
    |--------------------------------------------------------------------------
    */

    $router->get('/', [
        MainController::class,
        'index',
    ]);

    /*
    |--------------------------------------------------------------------------
    | Manga
    |--------------------------------------------------------------------------
    */

    $router->get('/manga', [
        MangaController::class,
        'index',
    ]);

    $router->get('/manga/collection/{page}', [
        MangaController::class,
        'collection',
    ]);

    $router->get('/manga/series/{slug}/{numero}', [
        MangaController::class,
        'show',
    ]);

    /*
    |--------------------------------------------------------------------------
    | Manga AJAX
    |--------------------------------------------------------------------------
    */

    $router->post(
        '/manga/ajax/update-note/{slug}/{numero}',
        [
            MangaAjaxController::class,
            'updateNote',
        ],
        [
            PostOnlyMiddleware::class,
            AjaxOnlyMiddleware::class,
            CsrfMiddleware::class,
        ]
    );

    $router->post(
        '/manga/ajax/update-lu/{slug}/{numero}',
        [
            MangaAjaxController::class,
            'updateLu',
        ],
        [
            PostOnlyMiddleware::class,
            AjaxOnlyMiddleware::class,
            CsrfMiddleware::class,
        ]
    );

    /*
    |--------------------------------------------------------------------------
    | Chinois
    |--------------------------------------------------------------------------
    */

    $router->get('/chinois', [
        ChinoisController::class,
        'index',
    ]);

    $router->get('/chinois/grammaire/hsk-1', [
        ChinoisController::class,
        'hsk1',
    ]);

    /*
    |--------------------------------------------------------------------------
    | Chinois AJAX
    |--------------------------------------------------------------------------
    */

    $router->post(
        '/chinois/ajax/toggle-grammaire-maitrise',
        [
            ChinoisAjaxController::class,
            'toggleGrammaireMaitrise',
        ],
        [
            PostOnlyMiddleware::class,
            AjaxOnlyMiddleware::class,
            CsrfMiddleware::class,
        ]
    );
};