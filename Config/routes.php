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

    /*
    |--------------------------------------------------------------------------
    | Anciennes URLs -> Redirections SEO
    |--------------------------------------------------------------------------
    */

    $router->get('/manga/collection', function (): void {
        redirect('manga/series', 301);
    });

    $router->get(
        '/manga/collection/{page}',
        function (string $page): void {
            redirect(
                'manga/series/' . rawurlencode($page),
                301
            );
        }
    );

    /*
    |--------------------------------------------------------------------------
    | Manga - Series
    |--------------------------------------------------------------------------
    */

    $router->get('/manga/series', [
        MangaController::class,
        'series',
    ]);

    $router->get('/manga/series/{page}', [
        MangaController::class,
        'series',
    ]);

    $router->get('/manga/lien', [
        MangaController::class,
        'lien',
    ]);

    $router->get('/manga/ajouter', [
        MangaController::class,
        'ajouter',
    ]);

    $router->post(
        '/manga/ajouter',
        [
            MangaController::class,
            'ajouterTraitement',
        ],
        [
            PostOnlyMiddleware::class,
            CsrfMiddleware::class,
        ]
    );

    $router->get('/manga/recherche', [
        MangaController::class,
        'recherche',
    ]);

    $router->get('/manga/recherche/{query}', [
        MangaController::class,
        'recherche',
    ]);

    /*
    |--------------------------------------------------------------------------
    | Manga - Serie
    |--------------------------------------------------------------------------
    */

    $router->get('/manga/series/{slug}', [
        MangaController::class,
        'serie',
    ]);

    $router->get('/manga/series/{slug}/{numero}', [
        MangaController::class,
        'show',
    ]);

    $router->get(
        '/manga/series/modifier/{slug}/{numero}',
        [
            MangaController::class,
            'modifier',
        ]
    );

    $router->post(
        '/manga/series/modifier/{slug}/{numero}',
        [
            MangaController::class,
            'update',
        ],
        [
            PostOnlyMiddleware::class,
            CsrfMiddleware::class,
        ]
    );

    $router->post(
        '/manga/series/supprimer/{slug}/{numero}',
        [
            MangaAjaxController::class,
            'delete',
        ],
        [
            PostOnlyMiddleware::class,
            AjaxOnlyMiddleware::class,
            CsrfMiddleware::class,
        ]
    );

    /*
    |--------------------------------------------------------------------------
    | Manga AJAX
    |--------------------------------------------------------------------------
    */

    $router->get(
        '/manga/ajax/series/{page}',
        [
            MangaAjaxController::class,
            'seriesPage',
        ],
        [
            AjaxOnlyMiddleware::class,
        ]
    );

    $router->get(
        '/manga/ajax/search/{query}',
        [
            MangaAjaxController::class,
            'search',
        ],
        [
            AjaxOnlyMiddleware::class,
        ]
    );

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

    $router->get('/chinois/grammaire/hsk-1', [
        ChinoisController::class,
        'hsk1',
    ]);

    $router->get('/chinois/grammaire/hsk-2', [
        ChinoisController::class,
        'hsk2',
    ]);

    $router->get('/chinois/grammaire/hsk-3', [
        ChinoisController::class,
        'hsk3',
    ]);

    $router->get('/chinois/grammaire/hsk-4', [
        ChinoisController::class,
        'hsk4',
    ]);

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
            PostOnlyMiddleware::class,
            AjaxOnlyMiddleware::class,
            CsrfMiddleware::class,
        ]
    );
};