<?php

declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\Chinois\ChinoisAjaxController;
use App\Controllers\Chinois\ChinoisController;
use App\Controllers\Figurine\FigurineController;
use App\Controllers\Figurine\FigurineAjaxController;
use App\Controllers\MainController;
use App\Controllers\Manga\MangaAjaxController;
use App\Controllers\Manga\MangaController;
use App\Controllers\Peluche\PelucheController;
use App\Controllers\Peluche\PelucheAjaxController;
use App\Controllers\Nendoroid\NendoroidController;
use App\Controllers\Nendoroid\NendoroidAjaxController;
use App\Controllers\ProfileController;
use App\Controllers\ProfileAjaxController;
use App\Controllers\Sql\SqlAjaxController;
use App\Controllers\Sql\SqlController;

use Framework\Application\App;
use Framework\Http\Middleware\AuthMiddleware;
use Framework\Http\Middleware\CsrfMiddleware;
use Framework\Http\Middleware\ExpectJsonMiddleware;
use Framework\Http\Middleware\GuestMiddleware;
use Framework\Routing\Router;

return static function (Router $router): void
{
    /*
    |--------------------------------------------------------------------------
    | AUTH
    |--------------------------------------------------------------------------
    */

    $router->get('connexion', [AuthController::class, 'login'], [GuestMiddleware::class]);

    $router->post('connexion', [AuthController::class, 'authenticate'], [GuestMiddleware::class, CsrfMiddleware::class]);

    if (! App::isProduction())
    {
        $router->get('inscription', [AuthController::class, 'register'], [GuestMiddleware::class]);

        $router->post('inscription', [AuthController::class, 'store'], [GuestMiddleware::class, CsrfMiddleware::class]);
    }

    /*
    |--------------------------------------------------------------------------
    | PROTECTED
    |--------------------------------------------------------------------------
    */

    $router->middleware(AuthMiddleware::class)->group(function (Router $router): void
    {
        $router->get('/', [MainController::class, 'index']);

        $router->get('profil', [ProfileController::class, 'index']);

        $router->get('profil/personnalisation', [ProfileController::class, 'customization']);

        $router->prefix('profil/ajax')->middleware(ExpectJsonMiddleware::class)->group(function (Router $router): void
        {
            $router->get('titles', [ProfileAjaxController::class, 'titles']);

            $router->get('avatars', [ProfileAjaxController::class, 'avatars']);

            $router->get('banners', [ProfileAjaxController::class, 'banners']);

            $router->get('frames', [ProfileAjaxController::class, 'frames']);
        });

        $router->prefix('profil/ajax')->middleware([ExpectJsonMiddleware::class, CsrfMiddleware::class])
            ->group(function (Router $router): void
        {
            $router->post('update-title', [ProfileAjaxController::class, 'updateTitle']);

            $router->post('update-avatar', [ProfileAjaxController::class, 'updateAvatar']);

            $router->post('update-banner', [ProfileAjaxController::class, 'updateBanner']);

            $router->post('update-frame', [ProfileAjaxController::class, 'updateFrame']);
        });

        /*
        |--------------------------------------------------------------------------
        | SQL
        |--------------------------------------------------------------------------
        */

        if (! App::isProduction())
        {
            $router->prefix('sql')->group(function (Router $router): void
            {
                $router->get('', [SqlController::class, 'index']);

                $router->post('', [SqlController::class, 'execute'], [CsrfMiddleware::class]);

                $router->prefix('ajax')->middleware([ExpectJsonMiddleware::class, CsrfMiddleware::class])
                    ->group(function (Router $router): void
                {
                    $router->post('execute', [SqlAjaxController::class, 'execute']);
                });
            });
        }

        $router->post('deconnexion', [AuthController::class, 'logout'], [CsrfMiddleware::class]);

        $router->prefix('manga')->group(function (Router $router): void
        {
            $router->get('', [MangaController::class, 'index']);

            $router->get('lien', [MangaController::class, 'links']);

            /*
            |--------------------------------------------------------------------------
            | AJOUT
            |--------------------------------------------------------------------------
            */

            $router->get(
                'ajouter',
                [MangaController::class, 'ajouter'],
            );

            $router->get(
                'ajouter/manga',
                [MangaController::class, 'create'],
            );

            $router->post(
                'ajouter/manga',
                [MangaController::class, 'store'],
                [CsrfMiddleware::class],
            );

            $router->get(
                'ajouter/artbook',
                [MangaController::class, 'createArtbook'],
            );

            $router->post(
                'ajouter/artbook',
                [MangaController::class, 'storeArtbook'],
                [CsrfMiddleware::class],
            );

            /*
            |--------------------------------------------------------------------------
            | ARTBOOKS
            |--------------------------------------------------------------------------
            */

            $router->prefix('artbooks')->group(function (Router $router): void
            {
                $router->get('', [MangaController::class, 'artbooks']);

                $router->get(
                    'page/{page:int}',
                    [MangaController::class, 'artbooks']
                );

                /*
                |--------------------------------------------------------------------------
                | ACTIONS SUR UN ARTBOOK
                |--------------------------------------------------------------------------
                */

                $router->get(
                    '{slug}/modifier/{numero:int}',
                    [MangaController::class, 'editArtbook']
                );

                $router->post(
                    '{slug}/modifier/{numero:int}',
                    [MangaController::class, 'updateArtbook'],
                    [CsrfMiddleware::class],
                );

                $router->post(
                    '{slug}/supprimer/{numero:int}',
                    [MangaAjaxController::class, 'deleteArtbook'],
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

                $router->get(
                    '{slug}/{numero:int}',
                    [MangaController::class, 'showArtbook']
                );
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

                $router->post('{slug}/modifier/{numero:int}', [MangaController::class, 'update'], [CsrfMiddleware::class]);

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
                $router->get(
                    'series/page/{page:int}',
                    [MangaAjaxController::class, 'seriesPage'],
                );

                $router->get(
                    'artbooks/page/{page:int}',
                    [MangaAjaxController::class, 'artbooksPage'],
                );
            });

            /*
            |--------------------------------------------------------------------------
            | AJAX JSON
            |--------------------------------------------------------------------------
            */

            $router->prefix('ajax')->middleware(ExpectJsonMiddleware::class)->group(function (Router $router): void
            {
                $router->get('recherche/{query}', [MangaAjaxController::class, 'search']);
            });

            /*
            |--------------------------------------------------------------------------
            | AJAX JSON + CSRF
            |--------------------------------------------------------------------------
            */

            $router->prefix('ajax')->middleware([ExpectJsonMiddleware::class, CsrfMiddleware::class])
                ->group(function (Router $router): void
            {
                $router->post('update-note/{slug}/{numero:int}', [MangaAjaxController::class, 'updateNote']);

                $router->post('update-read-status/{slug}/{numero:int}', [MangaAjaxController::class, 'updateReadStatus']);

                $router->post(
                    'artbook/update-read-status/{slug}/{numero:int}',
                    [MangaAjaxController::class, 'updateArtbookReadStatus'],
                );
            });
        });

        /*
        |--------------------------------------------------------------------------
        | FIGURINE
        |--------------------------------------------------------------------------
        */

        $router->prefix('figurine')->group(function (Router $router): void
        {
            $router->get('', [FigurineController::class, 'index']);

            $router->get('lien', [FigurineController::class, 'links']);

            $router->prefix('waifus')->group(function (Router $router): void
            {
                $router->get('', [FigurineController::class, 'waifus']);

                $router->get(
                    'page/{page:int}',
                    [FigurineController::class, 'waifus']
                );

                /*
                |--------------------------------------------------------------------------
                | ACTIONS SUR UNE FIGURINE
                |--------------------------------------------------------------------------
                */

                $router->get(
                    '{slug}/modifier/{numero:int}',
                    [FigurineController::class, 'edit']
                );

                $router->post(
                    '{slug}/modifier/{numero:int}',
                    [FigurineController::class, 'update'],
                    [CsrfMiddleware::class],
                );

                $router->post(
                    '{slug}/supprimer/{numero:int}',
                    [FigurineAjaxController::class, 'delete'],
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

                $router->get(
                    '{slug}/{numero:int}',
                    [FigurineController::class, 'showWaifu']
                );
            });

            /*
            |--------------------------------------------------------------------------
            | AJOUT
            |--------------------------------------------------------------------------
            */

            $router->get(
                'ajouter',
                [FigurineController::class, 'create']
            );

            $router->post(
                'ajouter',
                [FigurineController::class, 'store'],
                [CsrfMiddleware::class],
            );

            /*
            |--------------------------------------------------------------------------
            | AJAX HTML
            |--------------------------------------------------------------------------
            */

            $router->prefix('ajax')->group(function (Router $router): void
            {
                $router->get(
                    'waifus/page/{page:int}',
                    [FigurineAjaxController::class, 'waifusPage'],
                );
            });

            /*
            |--------------------------------------------------------------------------
            | AJAX JSON
            |--------------------------------------------------------------------------
            */

            $router->prefix('ajax')->middleware(ExpectJsonMiddleware::class)->group(function (Router $router): void
            {
                $router->get(
                    'recherche/{query}',
                    [FigurineAjaxController::class, 'search'],
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
            ])->group(function (Router $router): void
            {
                $router->post(
                    'update-collect-status/{slug}/{numero:int}',
                    [FigurineAjaxController::class, 'updateCollectStatus'],
                );
            });
        });

        /*
        |--------------------------------------------------------------------------
        | NENDOROIDS
        |--------------------------------------------------------------------------
        */

        $router->prefix('nendoroid')->group(function (Router $router): void
        {
            $router->get('', [NendoroidController::class, 'index']);

            $router->prefix('waifus')->group(function (Router $router): void
            {
                $router->get('', [NendoroidController::class, 'waifus']);

                $router->get(
                    'page/{page:int}',
                    [NendoroidController::class, 'waifus']
                );

                $router->get(
                    '{slug}/modifier/{numero:int}',
                    [NendoroidController::class, 'edit']
                );

                $router->post(
                    '{slug}/modifier/{numero:int}',
                    [NendoroidController::class, 'update'],
                    [CsrfMiddleware::class],
                );

                $router->post(
                    '{slug}/supprimer/{numero:int}',
                    [NendoroidAjaxController::class, 'delete'],
                    [
                        ExpectJsonMiddleware::class,
                        CsrfMiddleware::class,
                    ],
                );

                $router->get(
                    '{slug}/{numero:int}',
                    [NendoroidController::class, 'showWaifu']
                );
            });

            $router->get(
                'ajouter',
                [NendoroidController::class, 'create']
            );

            $router->post(
                'ajouter',
                [NendoroidController::class, 'store'],
                [CsrfMiddleware::class],
            );

            $router->prefix('ajax')->group(function (Router $router): void
            {
                $router->get(
                    'waifus/page/{page:int}',
                    [NendoroidAjaxController::class, 'waifusPage'],
                );
            });
        });

        /*
        |--------------------------------------------------------------------------
        | PELUCHE
        |--------------------------------------------------------------------------
        */

        $router->prefix('peluche')->group(function (Router $router): void
        {
            $router->get('', [PelucheController::class, 'index']);

            $router->prefix('waifus')->group(function (Router $router): void
            {
                $router->get('', [PelucheController::class, 'waifus']);

                $router->get(
                    'page/{page:int}',
                    [PelucheController::class, 'waifus']
                );

                /*
                |--------------------------------------------------------------------------
                | ACTIONS SUR UNE PELUCHE
                |--------------------------------------------------------------------------
                */

                $router->get(
                    '{slug}/modifier/{numero:int}',
                    [PelucheController::class, 'edit']
                );

                $router->post(
                    '{slug}/modifier/{numero:int}',
                    [PelucheController::class, 'update'],
                    [CsrfMiddleware::class],
                );

                $router->post(
                    '{slug}/supprimer/{numero:int}',
                    [PelucheAjaxController::class, 'delete'],
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

                $router->get(
                    '{slug}/{numero:int}',
                    [PelucheController::class, 'showWaifu']
                );
            });

            /*
            |--------------------------------------------------------------------------
            | AJOUT
            |--------------------------------------------------------------------------
            */

            $router->get(
                'ajouter',
                [PelucheController::class, 'create']
            );

            $router->post(
                'ajouter',
                [PelucheController::class, 'store'],
                [CsrfMiddleware::class],
            );

            /*
            |--------------------------------------------------------------------------
            | AJAX HTML
            |--------------------------------------------------------------------------
            */

            $router->prefix('ajax')->group(function (Router $router): void
            {
                $router->get(
                    'waifus/page/{page:int}',
                    [PelucheAjaxController::class, 'waifusPage'],
                );
            });
        });

        $router->prefix('chinois')->group(function (Router $router): void
        {
            $router->get('', [ChinoisController::class, 'index']);

            $router->get('vocabulaire', [ChinoisController::class, 'vocabulaire']);

            $router->get('vocabulaire/{langue}', [ChinoisController::class, 'langue']);

            $router->get('vocabulaire/{langue}/page/{page:int}', [ChinoisController::class, 'langue']);

            $router->get('grammaire', [ChinoisController::class, 'grammaire']);

            $router->get('grammaire/hsk{level:int}', [ChinoisController::class, 'hsk']);

            $router->get('vocabulaire/{langue}/recherche/{id:int}', [ChinoisController::class, 'showVocabulaire']);

            $router->get('grammaire/{niveau}/recherche/{id:int}', [ChinoisController::class, 'showGrammaire']);

            $router->get('flashcards', [ChinoisController::class, 'flashcards']);

            $router->get('flashcards/vocabulaire', [ChinoisController::class, 'flashcardsVocabulaire']);

            $router->get('flashcards/vocabulaire/modifier/{id:int}', [ChinoisController::class, 'editFlashcardVocabulaire']);

            $router->post('flashcards/vocabulaire/modifier/{id:int}', [ChinoisController::class, 'updateFlashcardVocabulaire'],
                [CsrfMiddleware::class]);

            $router->get('flashcards/grammaire', [ChinoisController::class, 'flashcardsGrammaire']);

            $router->get('flashcards/grammaire/modifier/{id:int}', [ChinoisController::class, 'editFlashcardGrammaire']);

            $router->post('flashcards/grammaire/modifier/{id:int}', [ChinoisController::class, 'updateFlashcardGrammaire'],
                [CsrfMiddleware::class]);

            /*
            |--------------------------------------------------------------------------
            | AJOUT
            |--------------------------------------------------------------------------
            */

            $router->get('ajouter', [ChinoisController::class, 'ajouter']);

            $router->get('ajouter/grammaire', [ChinoisController::class, 'createGrammaire']);

            $router->post('ajouter/grammaire', [ChinoisController::class, 'storeGrammaire'], [CsrfMiddleware::class]);

            $router->get('ajouter/vocabulaire', [ChinoisController::class, 'createVocabulaire']);

            $router->post('ajouter/vocabulaire', [ChinoisController::class, 'storeVocabulaire'], [CsrfMiddleware::class]);

            /*
            |--------------------------------------------------------------------------
            | EDITION
            |--------------------------------------------------------------------------
            */

            $router->get('grammaire/hsk{level:int}/modifier/{id:int}', [ChinoisController::class, 'editGrammaire']);

            $router->post('grammaire/hsk{level:int}/modifier/{id:int}', [ChinoisController::class, 'updateGrammaire'],
                [CsrfMiddleware::class]);

            $router->get('vocabulaire/{langue}/modifier/{id:int}', [ChinoisController::class, 'editVocabulaire']);

            $router->post('vocabulaire/{langue}/modifier/{id:int}', [ChinoisController::class, 'updateVocabulaire'],
                [CsrfMiddleware::class]);

            /*
            |--------------------------------------------------------------------------
            | AJAX JSON
            |--------------------------------------------------------------------------
            */

            $router->prefix('ajax')->middleware(ExpectJsonMiddleware::class)
                ->group(function (Router $router): void
            {
                $router->get('recherche/{query}', [ChinoisAjaxController::class, 'search']);
            });

            /*
            |--------------------------------------------------------------------------
            | AJAX JSON + CSRF
            |--------------------------------------------------------------------------
            */

            $router->prefix('ajax')->middleware([ExpectJsonMiddleware::class, CsrfMiddleware::class])
                ->group(function (Router $router): void
            {
                $router->post('toggle-grammaire-maitrise', [ChinoisAjaxController::class, 'toggleGrammaireMaitrise']);

                $router->post('toggle-vocabulaire-maitrise', [ChinoisAjaxController::class, 'toggleVocabulaireMaitrise']);

                $router->post('delete-grammaire', [ChinoisAjaxController::class, 'deleteGrammaire']);

                $router->post('delete-vocabulaire', [ChinoisAjaxController::class, 'deleteVocabulaire']);
            });
        });
    });
};
