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
    $router->get('/', [MainController::class, 'index']);

    $router->prefix('manga')->group(function (Router $router) {
        $router->get('', [MangaController::class, 'index']);
        $router->prefix('series')->group(function (Router $router) {
            $router->get('', [MangaController::class, 'series']);
            $router->get('page/{page:int}', [MangaController::class, 'series']);
            $router->get('{slug}/{numero:int}', [MangaController::class, 'show']);
            $router->get('{slug}', [MangaController::class, 'showSeries']);
            $router->get('modifier/{slug}/{numero:int}', [MangaController::class, 'edit']);
            $router->post('modifier/{slug}/{numero:int}', [MangaController::class, 'update'], [CsrfMiddleware::class]);
            $router->post('supprimer/{slug}/{numero:int}', [MangaAjaxController::class, 'delete'], [AjaxOnlyMiddleware::class, CsrfMiddleware::class]);
        });
        $router->get('recherche', [MangaController::class, 'search']);
        $router->get('recherche/{query}', [MangaController::class, 'search']);
        $router->get('ajouter', [MangaController::class, 'create']);
        $router->post('ajouter', [MangaController::class, 'store'], [CsrfMiddleware::class]);
        $router->get('lien', [MangaController::class, 'links']);

        $router->prefix('ajax')->middleware(AjaxOnlyMiddleware::class)->group(function (Router $router) {
            $router->get('series/page/{page:int}', [MangaAjaxController::class, 'seriesPage']);
            $router->get('recherche/{query}', [MangaAjaxController::class, 'search']);
            $router->post('update-note/{slug}/{numero:int}', [MangaAjaxController::class, 'updateNote'], [CsrfMiddleware::class]);
            $router->post('update-read-status/{slug}/{numero:int}', [MangaAjaxController::class, 'updateReadStatus'], [CsrfMiddleware::class]);
        });
    });

    $router->prefix('chinois')->group(function (Router $router) {
        $router->get('', [ChinoisController::class, 'index']);
        $router->get('mandarin', [ChinoisController::class, 'mandarin']);
        $router->get('jinyu', [ChinoisController::class, 'jinyu']);
        $router->get('grammaire', [ChinoisController::class, 'grammaire']);
        $router->get('grammaire/hsk{level:int}', [ChinoisController::class, 'hsk']);
        $router->get('flashcards', [ChinoisController::class, 'flashcards']);
        $router->get('ajouter', [ChinoisController::class, 'ajouter']);
        $router->prefix('ajax')->middleware([AjaxOnlyMiddleware::class, CsrfMiddleware::class])->group(function (Router $router) {
            $router->post('toggle-grammaire-maitrise', [ChinoisAjaxController::class, 'toggleGrammaireMaitrise']);
        });
    });
};