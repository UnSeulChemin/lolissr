<?php

declare(strict_types=1);

$tests[] = [

    'category' => 'Figurine',

    'label' => 'Accueil figurines',

    'path' => '/figurine',
];

$tests[] = [

    'category' => 'Figurine',

    'label' => 'Liens figurines',

    'path' => '/figurine/lien',
];

$tests[] = [

    'category' => 'Figurine',

    'label' => 'Liste des waifus',

    'path' => '/figurine/waifus',
];

$tests[] = [

    'category' => 'Figurine',

    'label' => 'Pagination waifus page 1',

    'path' => '/figurine/waifus/page/1',
];

$tests[] = [

    'category' => 'Figurine',

    'label' => 'Ajout figurine',

    'path' => '/figurine/ajouter',
];

/*
|--------------------------------------------------------------------------
| AJAX
|--------------------------------------------------------------------------
*/

$tests[] = [

    'category' => 'Figurine',

    'label' => 'Recherche JSON',

    'path' => '/figurine/ajax/recherche/test',

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

    'category' => 'Figurine',

    'label' => 'Pagination waifus HTML',

    'path' => '/figurine/ajax/waifus/page/1',

    'expected_status' => 200,

    'fragment' => true,
];