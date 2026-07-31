<?php

declare(strict_types=1);

use App\Controllers\Chinois\ChinoisAjaxController;
use App\Controllers\Chinois\ChinoisController;
use App\Controllers\Chinois\FlashcardsController;
use App\Controllers\Chinois\GrammaireController;
use App\Controllers\Chinois\VocabulaireController;

use Framework\Http\Middleware\CsrfMiddleware;
use Framework\Http\Middleware\ExpectJsonMiddleware;
use Framework\Routing\Router;

/** @var Router $router */

$router->prefix('chinois')->group(function (Router $router): void
{
    // =========================================
    // INDEX
    // =========================================

    $router->get('', [ChinoisController::class, 'index']);

    // =========================================
    // VOCABULAIRE
    // =========================================

    $router->prefix('vocabulaire')->group(function (Router $router): void
    {
        $router->get('', [VocabulaireController::class, 'index']);

        $router->get(
            '{langue}/page/{page:int}',
            [VocabulaireController::class, 'langue']
        );

        $router->get(
            '{langue}/recherche/{id:int}',
            [VocabulaireController::class, 'show']
        );

        $router->get(
            '{langue}/modifier/{id:int}',
            [VocabulaireController::class, 'edit']
        );

        $router->post(
            '{langue}/modifier/{id:int}',
            [VocabulaireController::class, 'update'],
            [CsrfMiddleware::class]
        );

        $router->get('{langue}', [VocabulaireController::class, 'langue']);
    });

    // =========================================
    // GRAMMAIRE
    // =========================================

    $router->prefix('grammaire')->group(function (Router $router): void
    {
        $router->get('', [GrammaireController::class, 'index']);

        $router->get(
            'hsk{level:int}',
            [GrammaireController::class, 'hsk']
        );

        $router->get(
            'hsk{level:int}/modifier/{id:int}',
            [GrammaireController::class, 'edit']
        );

        $router->post(
            'hsk{level:int}/modifier/{id:int}',
            [GrammaireController::class, 'update'],
            [CsrfMiddleware::class]
        );

        $router->get(
            '{niveau}/recherche/{id:int}',
            [GrammaireController::class, 'show']
        );
    });

    // =========================================
    // FLASHCARDS
    // =========================================

    $router->prefix('flashcards')->group(function (Router $router): void
    {
        $router->get('', [FlashcardsController::class, 'index']);

        $router->get(
            'vocabulaire',
            [FlashcardsController::class, 'vocabulaire']
        );

        $router->get(
            'grammaire',
            [FlashcardsController::class, 'grammaire']
        );
    });

    // =========================================
    // AJOUT
    // =========================================

    $router->prefix('ajouter')->group(function (Router $router): void
    {
        $router->get('', [ChinoisController::class, 'ajouter']);

        $router->get(
            'vocabulaire',
            [VocabulaireController::class, 'create']
        );

        $router->post(
            'vocabulaire',
            [VocabulaireController::class, 'store'],
            [CsrfMiddleware::class]
        );

        $router->get(
            'grammaire',
            [GrammaireController::class, 'create']
        );

        $router->post(
            'grammaire',
            [GrammaireController::class, 'store'],
            [CsrfMiddleware::class]
        );
    });

    // =========================================
    // AJAX
    // =========================================

    $router
        ->prefix('ajax')
        ->middleware(ExpectJsonMiddleware::class)
        ->group(function (Router $router): void
        {
            $router->get(
                'recherche/{query}',
                [ChinoisAjaxController::class, 'search']
            );

            $router
                ->middleware(CsrfMiddleware::class)
                ->group(function (Router $router): void
                {
                    $router->post(
                        'toggle-vocabulaire-maitrise',
                        [ChinoisAjaxController::class, 'toggleVocabulaireMaitrise']
                    );

                    $router->post(
                        'toggle-grammaire-maitrise',
                        [ChinoisAjaxController::class, 'toggleGrammaireMaitrise']
                    );

                    $router->post(
                        'delete-vocabulaire',
                        [ChinoisAjaxController::class, 'deleteVocabulaire']
                    );

                    $router->post(
                        'delete-grammaire',
                        [ChinoisAjaxController::class, 'deleteGrammaire']
                    );
                });
        });
});