<?php

return function ($router)
{
    /*
    |------------------------------------------------------------------
    | PRINCIPAL
    |------------------------------------------------------------------
    */

    $router->get('/', 'MainController@index');


    /*
    |------------------------------------------------------------------
    | MANGA — PAGES
    |------------------------------------------------------------------
    */

    /* Accueil manga */
    $router->get('/manga', 'MangaController@index');

    /* Collection */
    $router->get('/manga/collection', 'MangaController@collection');

    /* Pagination collection */
    $router->get('/manga/collection/page/{page}', 'MangaController@collection');

    /* Recherche */
    $router->get('/manga/recherche', 'MangaController@recherche');

    /* Série */
    $router->get('/manga/serie/{slug}', 'MangaController@serie');

    /* Page lien */
    $router->get('/manga/lien', 'MangaController@lien');


    /*
    |------------------------------------------------------------------
    | MANGA — FORMULAIRES
    |------------------------------------------------------------------
    */

    /* Ajouter */
    $router->get('/manga/ajouter', 'MangaController@ajouter');

    /* Modifier */
    $router->get('/manga/update/{slug}/{numero}', 'MangaController@modifier');


    /*
    |------------------------------------------------------------------
    | POST — TRAITEMENTS
    |------------------------------------------------------------------
    */

    $router->post('/manga/ajouter', 'MangaController@ajouterTraitement');

    $router->post('/manga/update/{slug}/{numero}', 'MangaController@update');


    /*
    |------------------------------------------------------------------
    | MANGA — DYNAMIQUE (TOUJOURS EN DERNIER)
    |------------------------------------------------------------------
    */

    /* Fiche manga */
    $router->get('/manga/{slug}/{numero}', 'MangaController@show');
};