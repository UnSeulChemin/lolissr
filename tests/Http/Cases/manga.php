<?php

declare(strict_types=1);

$tests[] = [

    'category' => 'Manga',

    'label' => 'Accueil manga',

    'path' => '/manga',
];

$tests[] = [

    'category' => 'Manga',

    'label' => 'Liens manga',

    'path' => '/manga/lien',
];

$tests[] = [

    'category' => 'Manga',

    'label' => 'Liste des séries',

    'path' => '/manga/series',
];

$tests[] = [

    'category' => 'Manga',

    'label' => 'Pagination séries page 1',

    'path' => '/manga/series/page/1',
];

$tests[] = [

    'category' => 'Manga',

    'label' => 'Liste des artbooks',

    'path' => '/manga/artbooks',
];

$tests[] = [

    'category' => 'Manga',

    'label' => 'Pagination artbooks page 1',

    'path' => '/manga/artbooks/page/1',
];

$tests[] = [

    'category' => 'Manga',

    'label' => 'Notes',

    'path' => '/manga/series/notes',
];

$tests[] = [

    'category' => 'Manga',

    'label' => 'À lire',

    'path' => '/manga/series/a-lire',
];

$tests[] = [

    'category' => 'Manga',

    'label' => 'Ajout',

    'path' => '/manga/ajouter',
];

$tests[] = [

    'category' => 'Manga',

    'label' => 'Ajout manga',

    'path' => '/manga/ajouter/manga',
];

$tests[] = [

    'category' => 'Manga',

    'label' => 'Ajout artbook',

    'path' => '/manga/ajouter/artbook',
];

/*
|--------------------------------------------------------------------------
| AJAX
|--------------------------------------------------------------------------
*/

$tests[] = [

    'category' => 'Manga',

    'label' => 'Recherche JSON',

    'path' => '/manga/ajax/recherche/test',

    'expected_status' => 200,

    'json' => true,

    'header_contains' => [
        'application/json',
    ],

    'headers' => [

        'Accept: application/json',

        'X-Requested-With: XMLHttpRequest',
    ],
];

$tests[] = [

    'category' => 'Manga',

    'label' => 'Pagination séries HTML',

    'path' => '/manga/ajax/series/page/1',

    'expected_status' => 200,

    'fragment' => true,
];

$tests[] = [

    'category' => 'Manga',

    'label' => 'Pagination artbooks HTML',

    'path' => '/manga/ajax/artbooks/page/1',

    'expected_status' => 200,

    'fragment' => true,
];