<?php

declare(strict_types=1);

use App\Core\Http\Router;
use App\Core\Http\Middleware\AjaxOnlyMiddleware;
use App\Core\Http\Middleware\PostOnlyMiddleware;
use App\Core\Http\Middleware\CsrfMiddleware;

return static function (Router $router): void
{
    /*
    |--------------------------------------------------------------------------
    | Principal
    |--------------------------------------------------------------------------
    */

    $router->get('/', 'MainController@index');

    /*
    |--------------------------------------------------------------------------
    | Manga — pages
    |--------------------------------------------------------------------------
    */

    $router->get('/manga', 'Manga\MangaController@index');
    $router->get('/manga/lien', 'Manga\MangaController@lien');

    $router->get('/manga/collection', 'Manga\MangaController@collection');
    $router->get('/manga/collection/page/{page}', 'Manga\MangaController@collection');

    $router->get('/manga/recherche', 'Manga\MangaController@recherche');
    $router->get('/manga/recherche/{query}', 'Manga\MangaController@recherche');

    $router->get('/manga/serie/{slug}', 'Manga\MangaController@serie');

    /*
    |--------------------------------------------------------------------------
    | Manga — AJAX
    |--------------------------------------------------------------------------
    */

    $router->get(
        '/manga/collection-ajax/page/{page}',
        'Manga\MangaAjaxController@collectionPage'
    )->middleware(AjaxOnlyMiddleware::class);

    $router->get(
        '/manga/search-ajax/{query}',
        'Manga\MangaAjaxController@search'
    )->middleware(AjaxOnlyMiddleware::class);

    $router->post(
        '/manga/ajax/update-note/{slug}/{numero}',
        'Manga\MangaAjaxController@updateNote'
    )->middleware([
        PostOnlyMiddleware::class,
        AjaxOnlyMiddleware::class,
        CsrfMiddleware::class,
    ]);

    $router->post(
        '/manga/ajax/supprimer/{slug}/{numero}',
        'Manga\MangaAjaxController@delete'
    )->middleware([
        PostOnlyMiddleware::class,
        AjaxOnlyMiddleware::class,
        CsrfMiddleware::class,
    ]);

    /*
    |--------------------------------------------------------------------------
    | Manga — formulaires
    |--------------------------------------------------------------------------
    */

    $router->get('/manga/ajouter', 'Manga\MangaController@ajouter');
    $router->get('/manga/modifier/{slug}/{numero}', 'Manga\MangaController@modifier');

    /*
    |--------------------------------------------------------------------------
    | POST — traitements HTML
    |--------------------------------------------------------------------------
    */

    $router->post(
        '/manga/ajouter',
        'Manga\MangaController@ajouterTraitement'
    )->middleware([
        PostOnlyMiddleware::class,
        CsrfMiddleware::class,
    ]);

    $router->post(
        '/manga/modifier/{slug}/{numero}',
        'Manga\MangaController@update'
    )->middleware([
        PostOnlyMiddleware::class,
        CsrfMiddleware::class,
    ]);

    /*
    |--------------------------------------------------------------------------
    | Manga — dynamique
    |--------------------------------------------------------------------------
    */

    $router->get('/manga/{slug}/{numero}', 'Manga\MangaController@show');
};