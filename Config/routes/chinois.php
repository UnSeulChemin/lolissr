<?php

declare(strict_types=1);

use App\Controllers\Chinois\ChinoisAjaxController;
use App\Controllers\Chinois\ChinoisController;

use Framework\Http\Middleware\CsrfMiddleware;
use Framework\Http\Middleware\ExpectJsonMiddleware;
use Framework\Routing\Router;

/** @var Router $router */

$router->prefix('chinois')->group(function (Router $router): void
{
    $router->get('', [ChinoisController::class, 'index']);

    /*
    |--------------------------------------------------------------------------
    | VOCABULAIRE
    |--------------------------------------------------------------------------
    */

    $router->get('vocabulaire', [ChinoisController::class, 'vocabulaire']);

    $router->get('vocabulaire/{langue}', [ChinoisController::class, 'langue']);

    $router->get('vocabulaire/{langue}/page/{page:int}', [ChinoisController::class, 'langue']);

    $router->get('vocabulaire/{langue}/recherche/{id:int}', [ChinoisController::class, 'showVocabulaire']);

    /*
    |--------------------------------------------------------------------------
    | GRAMMAIRE
    |--------------------------------------------------------------------------
    */

    $router->get('grammaire', [ChinoisController::class, 'grammaire']);

    $router->get('grammaire/hsk{level:int}', [ChinoisController::class, 'hsk']);

    $router->get('grammaire/{niveau}/recherche/{id:int}', [ChinoisController::class, 'showGrammaire']);

    /*
    |--------------------------------------------------------------------------
    | FLASHCARDS
    |--------------------------------------------------------------------------
    */

    $router->get('flashcards', [ChinoisController::class, 'flashcards']);

    $router->get('flashcards/vocabulaire', [ChinoisController::class, 'flashcardsVocabulaire']);

    $router->get('flashcards/vocabulaire/modifier/{id:int}', [ChinoisController::class, 'editFlashcardVocabulaire']);

    $router->post('flashcards/vocabulaire/modifier/{id:int}',
        [ChinoisController::class, 'updateFlashcardVocabulaire'],
        [CsrfMiddleware::class]
    );

    $router->get('flashcards/grammaire', [ChinoisController::class, 'flashcardsGrammaire']);

    $router->get('flashcards/grammaire/modifier/{id:int}', [ChinoisController::class, 'editFlashcardGrammaire']);

    $router->post('flashcards/grammaire/modifier/{id:int}',
        [ChinoisController::class, 'updateFlashcardGrammaire'],
        [CsrfMiddleware::class]
    );

    /*
    |--------------------------------------------------------------------------
    | AJOUT
    |--------------------------------------------------------------------------
    */

    $router->get('ajouter', [ChinoisController::class, 'ajouter']);

    $router->get('ajouter/vocabulaire', [ChinoisController::class, 'createVocabulaire']);

    $router->post('ajouter/vocabulaire', [ChinoisController::class, 'storeVocabulaire'], [CsrfMiddleware::class]);

    $router->get('ajouter/grammaire', [ChinoisController::class, 'createGrammaire']);

    $router->post('ajouter/grammaire', [ChinoisController::class, 'storeGrammaire'], [CsrfMiddleware::class]);

    /*
    |--------------------------------------------------------------------------
    | ÉDITION
    |--------------------------------------------------------------------------
    */
    
    $router->get('vocabulaire/{langue}/modifier/{id:int}', [ChinoisController::class, 'editVocabulaire']);

    $router->post('vocabulaire/{langue}/modifier/{id:int}',
        [ChinoisController::class, 'updateVocabulaire'],
        [CsrfMiddleware::class]
    );

    $router->get('grammaire/hsk{level:int}/modifier/{id:int}', [ChinoisController::class, 'editGrammaire']);

    $router->post('grammaire/hsk{level:int}/modifier/{id:int}',
        [ChinoisController::class, 'updateGrammaire'],
        [CsrfMiddleware::class]
    );

    /*
    |--------------------------------------------------------------------------
    | AJAX JSON
    |--------------------------------------------------------------------------
    */

    $router->prefix('ajax')
        ->middleware(ExpectJsonMiddleware::class)
        ->group(function (Router $router): void
        {
            $router->get('recherche/{query}', [ChinoisAjaxController::class, 'search']);
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
            $router->post('toggle-vocabulaire-maitrise', [ChinoisAjaxController::class, 'toggleVocabulaireMaitrise']);

            $router->post('toggle-grammaire-maitrise', [ChinoisAjaxController::class, 'toggleGrammaireMaitrise']);

            $router->post('delete-vocabulaire', [ChinoisAjaxController::class, 'deleteVocabulaire']);

            $router->post('delete-grammaire', [ChinoisAjaxController::class, 'deleteGrammaire']);
        });
});