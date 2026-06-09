<?php

declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\Chinois\ChinoisAjaxController;
use App\Controllers\Chinois\ChinoisController;
use App\Controllers\MainController;
use App\Controllers\Manga\MangaAjaxController;
use App\Controllers\Manga\MangaController;
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

    $router->get(
        'deconnexion',
        [AuthController::class, 'logout'],
    );

    /*
    |--------------------------------------------------------------------------
    | PROTECTED
    |--------------------------------------------------------------------------
    */

    $router->middleware(
        AuthMiddleware::class,
    )->group(function (Router $router): void {

        $router->get(
            '/',
            [MainController::class, 'index'],
        );

        $router->prefix('manga')->group(function (Router $router): void {

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
            | SERIES
            |--------------------------------------------------------------------------
            */

            $router->prefix('series')->group(function (Router $router): void {

                $router->get(
                    '',
                    [MangaController::class, 'series'],
                );

                $router->get(
                    'page/{page:int}',
                    [MangaController::class, 'series'],
                );

                $router->get(
                    'notes',
                    [MangaController::class, 'notes'],
                );

                $router->get(
                    '{slug}/{numero:int}',
                    [MangaController::class, 'show'],
                );

                $router->get(
                    '{slug}',
                    [MangaController::class, 'showSeries'],
                );

                $router->get(
                    'modifier/{slug}/{numero:int}',
                    [MangaController::class, 'edit'],
                );

                $router->post(
                    'modifier/{slug}/{numero:int}',
                    [MangaController::class, 'update'],
                    [CsrfMiddleware::class],
                );

                $router->post(
                    'supprimer/{slug}/{numero:int}',
                    [MangaAjaxController::class, 'delete'],
                    [
                        ExpectJsonMiddleware::class,
                        CsrfMiddleware::class,
                    ],
                );
            });
            /*
            |--------------------------------------------------------------------------
            | AJAX HTML FRAGMENTS
            |--------------------------------------------------------------------------
            */

            $router->prefix('ajax')->group(function (Router $router): void {

                $router->get(
                    'series/page/{page:int}',
                    [MangaAjaxController::class, 'seriesPage'],
                );
            });

            /*
            |--------------------------------------------------------------------------
            | AJAX JSON
            |--------------------------------------------------------------------------
            */

            $router->prefix('ajax')
                ->middleware(
                    ExpectJsonMiddleware::class,
                )
                ->group(function (Router $router): void {

                    $router->get(
                        'recherche/{query}',
                        [MangaAjaxController::class, 'search'],
                    );
                });

            /*
            |--------------------------------------------------------------------------
            | AJAX JSON + CSRF
            |--------------------------------------------------------------------------
            */

            $router->prefix('ajax')->middleware([
                ExpectJsonMiddleware::class,
                CsrfMiddleware::class,
            ])->group(function (Router $router): void {

                $router->post(
                    'update-note/{slug}/{numero:int}',
                    [MangaAjaxController::class, 'updateNote'],
                );

                $router->post(
                    'update-read-status/{slug}/{numero:int}',
                    [MangaAjaxController::class, 'updateReadStatus'],
                );
            });
        });

        $router->prefix('chinois')->group(function (Router $router): void {

            $router->get('', [ChinoisController::class, 'index']);

            $router->get(
                'mandarin',
                [ChinoisController::class, 'mandarin'],
            );

            $router->get(
                'jinyu',
                [ChinoisController::class, 'jinyu'],
            );

            $router->get(
                'grammaire',
                [ChinoisController::class, 'grammaire'],
            );

            $router->get(
                'grammaire/hsk{level:int}',
                [ChinoisController::class, 'hsk'],
            );

            $router->get(
                'flashcards',
                [ChinoisController::class, 'flashcards'],
            );

            $router->get(
                'flashcards/vocabulaire',
                [ChinoisController::class, 'flashcardsVocabulaire'],
            );

            $router->get(
                'flashcards/grammaire',
                [ChinoisController::class, 'flashcardsGrammaire'],
            );

            $router->get(
                'ajouter',
                [ChinoisController::class, 'ajouter'],
            );

            $router->get(
                'grammaire/ajouter',
                [ChinoisController::class, 'createGrammaire'],
            );

            $router->get(
                'grammaire/modifier/{id:int}',
                [ChinoisController::class, 'editGrammaire'],
            );

            $router->post(
                'grammaire/modifier/{id:int}',
                [ChinoisController::class, 'updateGrammaire'],
                [CsrfMiddleware::class],
            );

            $router->post(
                'grammaire/ajouter',
                [ChinoisController::class, 'storeGrammaire'],
                [CsrfMiddleware::class],
            );

            $router->get(
                'vocabulaire/ajouter',
                [ChinoisController::class, 'createVocabulaire'],
            );

            $router->get(
                'vocabulaire/modifier/{id:int}',
                [ChinoisController::class, 'editVocabulaire'],
            );

            $router->post(
                'vocabulaire/modifier/{id:int}',
                [ChinoisController::class, 'updateVocabulaire'],
                [CsrfMiddleware::class],
            );

            $router->post(
                'vocabulaire/ajouter',
                [ChinoisController::class, 'storeVocabulaire'],
                [CsrfMiddleware::class],
            );

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