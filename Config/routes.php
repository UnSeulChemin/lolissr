<?php

declare(strict_types=1);

use App\Core\Router;

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
    $router->get('/manga/collection-ajax/page/{page}', 'MangaController@collectionAjax');

    $router->get('/manga/recherche', 'MangaController@recherche');
    $router->get('/manga/recherche/{query}', 'MangaController@recherche');
    $router->get('/manga/search-ajax/{query}', 'MangaController@searchAjax');

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

    $router->post('/manga/ajouter', 'MangaController@ajouterTraitement');
    $router->post('/manga/modifier/{slug}/{numero}', 'MangaController@update');
    $router->post('/manga/ajax/update-note/{slug}/{numero}', 'MangaController@ajaxUpdateNote');

    /*
    |--------------------------------------------------------------------------
    | Manga — dynamique
    |--------------------------------------------------------------------------
    |
    | Toujours en dernier pour éviter de capter les routes plus spécifiques.
    |
    */

    $router->get('/manga/{slug}/{numero}', 'MangaController@show');
};