<?php

declare(strict_types=1);

use App\Controllers\Chinois\ChinoisAjaxController;
use App\Controllers\Chinois\ChinoisController;
use App\Controllers\MainController;
use App\Controllers\Manga\MangaAjaxController;
use App\Controllers\Manga\MangaController;

use Framework\Http\Middleware\AjaxOnlyMiddleware;
use Framework\Http\Middleware\CsrfMiddleware;

use Framework\Routing\Router;

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

    /*
    |--------------------------------------------------------------------------
    | Manga - Series
    |--------------------------------------------------------------------------
    |
    | IMPORTANT:
    | Specific routes MUST be declared before dynamic routes.
    |
    | Correct order:
    | - /manga/series/page/{page}
    | - /manga/series/{slug}
    |
    */

    $router->get('/manga/series', [
        MangaController::class,
        'series',
    ]);

    $router->get('/manga/series/page/{page:int}', [
        MangaController::class,
        'series',
    ]);

    /*
    |--------------------------------------------------------------------------
    | Manga - Search
    |--------------------------------------------------------------------------
    |
    | Example:
    | /manga/recherche/berserk
    |
    */

    $router->get('/manga/recherche', [
        MangaController::class,
        'search',
    ]);

    $router->get('/manga/recherche/{query}', [
        MangaController::class,
        'search',
    ]);

    /*
    |--------------------------------------------------------------------------
    | Manga - Create
    |--------------------------------------------------------------------------
    */

    $router->get('/manga/ajouter', [
        MangaController::class,
        'create',
    ]);

    $router->post(
        '/manga/ajouter',
        [
            MangaController::class,
            'store',
        ],
        [
            CsrfMiddleware::class,
        ],
    );

    /*
    |--------------------------------------------------------------------------
    | Manga - Update
    |--------------------------------------------------------------------------
    */

    $router->get(
        '/manga/series/modifier/{slug}/{numero:int}',
        [
            MangaController::class,
            'edit',
        ],
    );

    $router->post(
        '/manga/series/modifier/{slug}/{numero:int}',
        [
            MangaController::class,
            'update',
        ],
        [
            CsrfMiddleware::class,
        ],
    );

    /*
    |--------------------------------------------------------------------------
    | Manga - Delete
    |--------------------------------------------------------------------------
    */

    $router->post(
        '/manga/series/supprimer/{slug}/{numero:int}',
        [
            MangaAjaxController::class,
            'delete',
        ],
        [
            AjaxOnlyMiddleware::class,
            CsrfMiddleware::class,
        ],
    );

    /*
    |--------------------------------------------------------------------------
    | Manga - Misc
    |--------------------------------------------------------------------------
    */

    $router->get('/manga/lien', [
        MangaController::class,
        'links',
    ]);

    /*
    |--------------------------------------------------------------------------
    | Manga - Show
    |--------------------------------------------------------------------------
    */

    $router->get('/manga/series/{slug}/{numero:int}', [
        MangaController::class,
        'show',
    ]);

    $router->get('/manga/series/{slug}', [
        MangaController::class,
        'showSeries',
    ]);

    /*
    |--------------------------------------------------------------------------
    | Manga AJAX
    |--------------------------------------------------------------------------
    */

    $router->get(
        '/manga/ajax/series/page/{page:int}',
        [
            MangaAjaxController::class,
            'seriesPage',
        ],
        [
            AjaxOnlyMiddleware::class,
        ],
    );

    /*
    |--------------------------------------------------------------------------
    | Manga AJAX - Search
    |--------------------------------------------------------------------------
    |
    | Example:
    | /manga/ajax/recherche/berserk
    |
    */

    $router->get(
        '/manga/ajax/recherche/{query}',
        [
            MangaAjaxController::class,
            'search',
        ],
        [
            AjaxOnlyMiddleware::class,
        ],
    );

    $router->post(
        '/manga/ajax/update-note/{slug}/{numero:int}',
        [
            MangaAjaxController::class,
            'updateNote',
        ],
        [
            AjaxOnlyMiddleware::class,
            CsrfMiddleware::class,
        ],
    );

    $router->post(
        '/manga/ajax/update-read-status/{slug}/{numero:int}',
        [
            MangaAjaxController::class,
            'updateReadStatus',
        ],
        [
            AjaxOnlyMiddleware::class,
            CsrfMiddleware::class,
        ],
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

    /*
    |--------------------------------------------------------------------------
    | Chinois - Langues
    |--------------------------------------------------------------------------
    */

    $router->get('/chinois/mandarin', [
        ChinoisController::class,
        'mandarin',
    ]);

    $router->get('/chinois/jinyu', [
        ChinoisController::class,
        'jinyu',
    ]);

    /*
    |--------------------------------------------------------------------------
    | Chinois - Grammaire
    |--------------------------------------------------------------------------
    */

    $router->get('/chinois/grammaire', [
        ChinoisController::class,
        'grammaire',
    ]);

    $router->get(
        '/chinois/grammaire/hsk{level:int}',
        [
            ChinoisController::class,
            'hsk',
        ],
    );

    /*
    |--------------------------------------------------------------------------
    | Chinois - Outils
    |--------------------------------------------------------------------------
    */

    $router->get('/chinois/flashcards', [
        ChinoisController::class,
        'flashcards',
    ]);

    $router->get('/chinois/ajouter', [
        ChinoisController::class,
        'ajouter',
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
            AjaxOnlyMiddleware::class,
            CsrfMiddleware::class,
        ],
    );
};
