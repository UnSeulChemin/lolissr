<?php

return function ($router)
{
    /*
    |--------------------------------------------------------------------------
    | Routes GET
    |--------------------------------------------------------------------------
    */

    $router->get('/', 'MainController@index');

    $router->get('/manga', 'MangaController@index');

    $router->get('/manga/collection', 'MangaController@collection');
    $router->get('/manga/collection/page/{page}', 'MangaController@collection');

    $router->get('/manga/serie/{slug}', 'MangaController@serie');

    $router->get('/manga/ajouter', 'MangaController@ajouter');
    $router->get('/manga/lien', 'MangaController@lien');

    $router->get('/manga/update/{slug}/{numero}', 'MangaController@modifier');
    $router->get('/manga/{slug}/{numero}', 'MangaController@show');

    /*
    |--------------------------------------------------------------------------
    | Routes POST
    |--------------------------------------------------------------------------
    */

    $router->post('/manga/ajouter', 'MangaController@ajouterTraitement');
    $router->post('/manga/update/{slug}/{numero}', 'MangaController@update');
};