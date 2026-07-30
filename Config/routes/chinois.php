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

    $router->get('vocabulaire', [VocabulaireController::class, 'index']);

    $router->get(
        'vocabulaire/{langue}/page/{page:int}',
        [VocabulaireController::class, 'langue']
    );

    $router->get(
        'vocabulaire/{langue}/recherche/{id:int}',
        [VocabulaireController::class, 'show']
    );

    $router->get(
        'vocabulaire/{langue}/modifier/{id:int}',
        [VocabulaireController::class, 'edit']
    );

    $router->post(
        'vocabulaire/{langue}/modifier/{id:int}',
        [VocabulaireController::class, 'update'],
        [CsrfMiddleware::class]
    );

    $router->get(
        'vocabulaire/{langue}',
        [VocabulaireController::class, 'langue']
    );

    // =========================================
    // GRAMMAIRE
    // =========================================

    $router->get('grammaire', [GrammaireController::class, 'index']);

    $router->get(
        'grammaire/hsk{level:int}',
        [GrammaireController::class, 'hsk']
    );

    $router->get(
        'grammaire/hsk{level:int}/modifier/{id:int}',
        [GrammaireController::class, 'edit']
    );

    $router->post(
        'grammaire/hsk{level:int}/modifier/{id:int}',
        [GrammaireController::class, 'update'],
        [CsrfMiddleware::class]
    );

    $router->get(
        'grammaire/{niveau}/recherche/{id:int}',
        [GrammaireController::class, 'show']
    );

    // =========================================
    // FLASHCARDS
    // =========================================

    $router->get('flashcards', [FlashcardsController::class, 'index']);

    $router->get(
        'flashcards/vocabulaire',
        [FlashcardsController::class, 'vocabulaire']
    );

    $router->get(
        'flashcards/grammaire',
        [FlashcardsController::class, 'grammaire']
    );

    // =========================================
    // AJOUT
    // =========================================

    $router->get('ajouter', [ChinoisController::class, 'ajouter']);

    $router->get(
        'ajouter/vocabulaire',
        [VocabulaireController::class, 'create']
    );

    $router->post(
        'ajouter/vocabulaire',
        [VocabulaireController::class, 'store'],
        [CsrfMiddleware::class]
    );

    $router->get(
        'ajouter/grammaire',
        [GrammaireController::class, 'create']
    );

    $router->post(
        'ajouter/grammaire',
        [GrammaireController::class, 'store'],
        [CsrfMiddleware::class]
    );

    // =========================================
    // AJAX
    // =========================================

    $router->prefix('ajax')
        ->middleware(ExpectJsonMiddleware::class)
        ->group(function (Router $router): void
        {
            $router->get(
                'recherche/{query}',
                [ChinoisAjaxController::class, 'search']
            );

            $router->post(
                'toggle-vocabulaire-maitrise',
                [ChinoisAjaxController::class, 'toggleVocabulaireMaitrise'],
                [CsrfMiddleware::class]
            );

            $router->post(
                'toggle-grammaire-maitrise',
                [ChinoisAjaxController::class, 'toggleGrammaireMaitrise'],
                [CsrfMiddleware::class]
            );

            $router->post(
                'delete-vocabulaire',
                [ChinoisAjaxController::class, 'deleteVocabulaire'],
                [CsrfMiddleware::class]
            );

            $router->post(
                'delete-grammaire',
                [ChinoisAjaxController::class, 'deleteGrammaire'],
                [CsrfMiddleware::class]
            );
        });
});