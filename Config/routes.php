<?php

declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\MainController;
use App\Controllers\Sql\SqlController;
use App\Controllers\Sql\SqlAjaxController;
use App\Controllers\Chinois\ChinoisAjaxController;
use App\Controllers\Chinois\ChinoisController;
use App\Controllers\Manga\MangaAjaxController;
use App\Controllers\Manga\MangaController;
use App\Controllers\ProfileController;

use Framework\Http\Middleware\AuthMiddleware;
use Framework\Http\Middleware\CsrfMiddleware;
use Framework\Http\Middleware\ExpectJsonMiddleware;
use Framework\Http\Middleware\GuestMiddleware;
use Framework\Routing\Router;

return static function (Router $router): void {

    /*
    |--------------------------------------------------------------------------
    | AUTH
    |--------------------------------------------------------------------------
    */

    $router->get(
        'connexion',
        [AuthController::class, 'login'],
        [GuestMiddleware::class],
    );

    $router->post(
        'connexion',
        [AuthController::class, 'authenticate'],
        [
            GuestMiddleware::class,
            CsrfMiddleware::class,
        ],
    );

    if (
        config('app.env') !== 'production'
    ) {

        $router->get(
            'inscription',
            [AuthController::class, 'register'],
            [GuestMiddleware::class],
        );

        $router->post(
            'inscription',
            [AuthController::class, 'store'],
            [
                GuestMiddleware::class,
                CsrfMiddleware::class,
            ],
        );
    }

    /*
    |--------------------------------------------------------------------------
    | PROTECTED
    |--------------------------------------------------------------------------
    */

    $router->middleware(
        AuthMiddleware::class,
    )->group(function (Router $router): void {

        $router->get('/', [MainController::class, 'index']);

        $router->get('profil', [ProfileController::class, 'index']);

        /*
        |--------------------------------------------------------------------------
        | SQL
        |--------------------------------------------------------------------------
        */

        if (
            config('app.env') !== 'production'
        )
        {
            $router->prefix('sql')->group(function (Router $router): void {

                $router->get('', [SqlController::class, 'index']);

                $router->post(
                    '',
                    [SqlController::class, 'execute'],
                    [
                        CsrfMiddleware::class,
                    ],
                );

                $router->prefix('ajax')
                    ->middleware([
                        ExpectJsonMiddleware::class,
                        CsrfMiddleware::class,
                    ])
                    ->group(function (Router $router): void {

                        $router->post(
                            'execute',
                            [SqlAjaxController::class, 'execute'],
                        );
                    });
            });
        }

        $router->post(
            'deconnexion',
            [AuthController::class, 'logout'],
            [
                CsrfMiddleware::class,
            ],
        );

        $router->prefix('manga')->group(function (Router $router): void
        {
            $router->get('', [MangaController::class, 'index']);

            $router->get('recherche', [MangaController::class, 'search']);

            $router->get('recherche/{query}', [MangaController::class, 'search']);

            $router->get('ajouter', [MangaController::class, 'create']);

            $router->post(
                'ajouter',
                [MangaController::class, 'store'],
                [CsrfMiddleware::class],
            );

            $router->get('lien', [MangaController::class, 'links']);

            /*
            |--------------------------------------------------------------------------
            | ARTBOOKS
            |--------------------------------------------------------------------------
            */

            $router->get('artbooks', [MangaController::class, 'artbooks']);

            $router->get('artbooks/{slug}', [MangaController::class, 'showArtbook']);

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

                $router->post(
                    '{slug}/modifier/{numero:int}',
                    [MangaController::class, 'update'],
                    [CsrfMiddleware::class],
                );

                $router->post(
                    '{slug}/supprimer/{numero:int}',
                    [MangaAjaxController::class, 'delete'],
                    [
                        ExpectJsonMiddleware::class,
                        CsrfMiddleware::class,
                    ],
                );

                /*
                |--------------------------------------------------------------------------
                | CONSULTATION
                |--------------------------------------------------------------------------
                */

                $router->get('{slug}/{numero:int}', [MangaController::class, 'show']);

                $router->get('{slug}', [MangaController::class, 'showSeries']);

            });

            /*
            |--------------------------------------------------------------------------
            | AJAX HTML FRAGMENTS
            |--------------------------------------------------------------------------
            */

            $router->prefix('ajax')->group(function (Router $router): void
            {
                $router->get('series/page/{page:int}', [MangaAjaxController::class, 'seriesPage']);
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
            });
        });

        $router->prefix('chinois')->group(function (Router $router): void {

            $router->get('', [ChinoisController::class, 'index']);

            $router->get('vocabulaire', [ChinoisController::class, 'vocabulaire']);

            $router->get('vocabulaire/{langue}', [ChinoisController::class, 'langue']);

            $router->get('grammaire', [ChinoisController::class, 'grammaire']);

            $router->get('grammaire/hsk{level:int}', [ChinoisController::class, 'hsk']);

            $router->get('vocabulaire/{langue}/recherche/{id:int}', [ChinoisController::class, 'showVocabulaire']);

            $router->get('grammaire/{niveau}/recherche/{id:int}', [ChinoisController::class, 'showGrammaire']);

            $router->get('flashcards', [ChinoisController::class, 'flashcards']);

            $router->get('flashcards/vocabulaire', [ChinoisController::class, 'flashcardsVocabulaire']);

            $router->get('flashcards/vocabulaire/modifier/{id:int}', [ChinoisController::class, 'editFlashcardVocabulaire']);

            $router->post(
                'flashcards/vocabulaire/modifier/{id:int}',
                [ChinoisController::class, 'updateFlashcardVocabulaire'],
                [CsrfMiddleware::class],
            );

            $router->get(
                'flashcards/grammaire',
                [ChinoisController::class, 'flashcardsGrammaire'],
            );

            $router->get(
                'flashcards/grammaire/modifier/{id:int}',
                [ChinoisController::class, 'editFlashcardGrammaire'],
            );

            $router->post(
                'flashcards/grammaire/modifier/{id:int}',
                [ChinoisController::class, 'updateFlashcardGrammaire'],
                [CsrfMiddleware::class],
            );

            $router->get(
                'ajouter',
                [ChinoisController::class, 'ajouter'],
            );

            $router->get(
                'ajouter/grammaire',
                [ChinoisController::class, 'createGrammaire'],
            );

            $router->post(
                'ajouter/grammaire',
                [ChinoisController::class, 'storeGrammaire'],
                [CsrfMiddleware::class],
            );

            $router->get(
                'ajouter/vocabulaire',
                [ChinoisController::class, 'createVocabulaire'],
            );

            $router->post(
                'ajouter/vocabulaire',
                [ChinoisController::class, 'storeVocabulaire'],
                [CsrfMiddleware::class],
            );

            $router->get(
                'grammaire/hsk{level:int}/modifier/{id:int}',
                [ChinoisController::class, 'editGrammaire'],
            );

            $router->post(
                'grammaire/hsk{level:int}/modifier/{id:int}',
                [ChinoisController::class, 'updateGrammaire'],
                [CsrfMiddleware::class],
            );

            $router->get(
                'vocabulaire/{langue}/modifier/{id:int}',
                [ChinoisController::class, 'editVocabulaire'],
            );

            $router->post(
                'vocabulaire/{langue}/modifier/{id:int}',
                [ChinoisController::class, 'updateVocabulaire'],
                [CsrfMiddleware::class],
            );

            $router->prefix('ajax')
                ->middleware(
                    ExpectJsonMiddleware::class,
                )
                ->group(function (Router $router): void {

                    $router->get(
                        'recherche/{query}',
                        [ChinoisAjaxController::class, 'search'],
                    );
                });
            
            $router->prefix('ajax')->middleware([
                ExpectJsonMiddleware::class,
                CsrfMiddleware::class,
            ])->group(function (Router $router): void {

                $router->post(
                    'toggle-grammaire-maitrise',
                    [ChinoisAjaxController::class, 'toggleGrammaireMaitrise'],
                );

                $router->post(
                    'toggle-vocabulaire-maitrise',
                    [ChinoisAjaxController::class, 'toggleVocabulaireMaitrise'],
                );

                $router->post(
                    'delete-grammaire',
                    [ChinoisAjaxController::class, 'deleteGrammaire'],
                );

                $router->post(
                    'delete-vocabulaire',
                    [ChinoisAjaxController::class, 'deleteVocabulaire'],
                );
            });
        });

    });
};