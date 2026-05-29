<?php

declare(strict_types=1);

$tests[] = [

    'category' => 'AJAX',

    'label' => 'Recherche AJAX retourne 200',

    'path' => '/manga/ajax/recherche/love',

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

    'category' => 'AJAX',

    'label' => 'Pagination AJAX retourne 200',

    'path' => '/manga/ajax/series/page/1',

    'expected_status' => 200,

    'headers' => [

        'Accept: application/json',

        'X-Requested-With: XMLHttpRequest',
    ],
];