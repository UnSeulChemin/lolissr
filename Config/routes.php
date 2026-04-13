<?php

/*
|--------------------------------------------------------------------------
| Fichier des routes de l'application
|--------------------------------------------------------------------------
|
| Toutes les routes du site sont centralisées ici.
|
| Syntaxe :
|   $router->get('/url', 'Controller@method');
|   $router->post('/url', 'Controller@method');
|
| Règle importante :
| Les routes les plus spécifiques doivent être déclarées avant
| les routes dynamiques plus générales.
|
*/

return function ($router)
{
    /*
    |--------------------------------------------------------------------------
    | ROUTE PRINCIPALE
    |--------------------------------------------------------------------------
    */

    // Accueil du site
    $router->get('/', 'MainController@index');


    /*
    |--------------------------------------------------------------------------
    | ROUTES MANGA — PAGES PRINCIPALES
    |--------------------------------------------------------------------------
    */

    // Accueil manga
    $router->get('/manga', 'MangaController@index');

    // Collection manga
    $router->get('/manga/collection', 'MangaController@collection');

    // Pagination de la collection
    // Exemple : /manga/collection/page/2
    $router->get('/manga/collection/page/{page}', 'MangaController@collection');

    // Page d'une série
    // Exemple : /manga/serie/one-piece
    $router->get('/manga/serie/{slug}', 'MangaController@serie');


    /*
    |--------------------------------------------------------------------------
    | ROUTES MANGA — FORMULAIRES ET ACTIONS
    |--------------------------------------------------------------------------
    */

    // Formulaire d'ajout
    $router->get('/manga/ajouter', 'MangaController@ajouter');

    // Page lien
    $router->get('/manga/lien', 'MangaController@lien');

    // Formulaire de modification
    // Exemple : /manga/update/one-piece/12
    $router->get('/manga/update/{slug}/{numero}', 'MangaController@modifier');


    /*
    |--------------------------------------------------------------------------
    | ROUTES MANGA — AFFICHAGE DYNAMIQUE
    |--------------------------------------------------------------------------
    |
    | Cette route est volontairement placée après les autres,
    | car elle est plus générale.
    |
    */

    // Fiche d'un manga
    // Exemple : /manga/one-piece/12
    $router->get('/manga/{slug}/{numero}', 'MangaController@show');


    /*
    |--------------------------------------------------------------------------
    | ROUTES POST — TRAITEMENTS FORMULAIRES
    |--------------------------------------------------------------------------
    */

    // Traitement du formulaire d'ajout
    $router->post('/manga/ajouter', 'MangaController@ajouterTraitement');

    // Traitement du formulaire de modification
    $router->post('/manga/update/{slug}/{numero}', 'MangaController@update');
};