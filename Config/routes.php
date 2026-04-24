<?php

declare(strict_types=1);

use App\Core\Http\Router;
use App\Core\Http\Middleware\AjaxOnlyMiddleware;
use App\Core\Http\Middleware\PostOnlyMiddleware;

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

    $router->get('/manga', 'MangaController@index');
    $router->get('/manga/lien', 'MangaController@lien');

    $router->get('/manga/collection', 'MangaController@collection');
    $router->get('/manga/collection/page/{page}', 'MangaController@collection');
    $router->get('/manga/collection-ajax/page/{page}', 'MangaController@collectionAjax')
        ->middleware(AjaxOnlyMiddleware::class);

    $router->get('/manga/recherche', 'MangaController@recherche');
    $router->get('/manga/recherche/{query}', 'MangaController@recherche');
    $router->get('/manga/search-ajax/{query}', 'MangaController@searchAjax')
        ->middleware(AjaxOnlyMiddleware::class);

    $router->get('/manga/serie/{slug}', 'MangaController@serie');

    /*
    |--------------------------------------------------------------------------
    | Manga — formulaires
    |--------------------------------------------------------------------------
    */

    $router->get('/manga/ajouter', 'MangaController@ajouter');
    $router->get('/manga/modifier/{slug}/{numero}', 'MangaController@modifier');

    /*
    |--------------------------------------------------------------------------
    | POST — traitements
    |--------------------------------------------------------------------------
    */

    $router->post('/manga/ajouter', 'MangaController@ajouterTraitement')
        ->middleware(PostOnlyMiddleware::class);

    $router->post('/manga/modifier/{slug}/{numero}', 'MangaController@update')
        ->middleware(PostOnlyMiddleware::class);

    $router->post('/manga/ajax/update-note/{slug}/{numero}', 'MangaController@ajaxUpdateNote')
        ->middleware([
            PostOnlyMiddleware::class,
            AjaxOnlyMiddleware::class,
        ]);

    $router->post('/manga/ajax/supprimer/{slug}/{numero}', 'MangaController@ajaxDelete')
        ->middleware([
            PostOnlyMiddleware::class,
            AjaxOnlyMiddleware::class,
        ]);

    /*
    |--------------------------------------------------------------------------
    | Manga — dynamique
    |--------------------------------------------------------------------------
    */

    $router->get('/manga/{slug}/{numero}', 'MangaController@show');
};