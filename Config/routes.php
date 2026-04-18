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

    /* Page lien */
    $router->get('/manga/lien', 'MangaController@lien');

    /* Collection */
    $router->get('/manga/collection', 'MangaController@collection');

    /* Pagination collection */
    $router->get('/manga/collection/page/{page}', 'MangaController@collection');

    /* Collection AJAX */
    $router->get('/manga/collection-ajax/page/{page}', 'MangaController@collectionAjax');

    /* Recherche */
    $router->get('/manga/recherche', 'MangaController@recherche');
    $router->get('/manga/recherche/{query}', 'MangaController@recherche');

    /* Recherche AJAX */
    $router->get('/manga/search-ajax/{query}', 'MangaController@searchAjax');

    /* Série */
    $router->get('/manga/serie/{slug}', 'MangaController@serie');


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

    /* Update note AJAX */
    $router->post('/manga/ajax/update-note/{slug}/{numero}', 'MangaController@ajaxUpdateNote');


    /*
    |------------------------------------------------------------------
    | MANGA — DYNAMIQUE (TOUJOURS EN DERNIER)
    |------------------------------------------------------------------
    */

    /* Fiche manga */
    $router->get('/manga/{slug}/{numero}', 'MangaController@show');
};