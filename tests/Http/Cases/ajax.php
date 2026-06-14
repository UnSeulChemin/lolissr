<?php

declare(strict_types=1);

$tests[] = [

    'category' => 'Ajax',

    'label' => 'Recherche manga JSON',

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

    'category' => 'Ajax',

    'label' => 'Pagination séries HTML',

    'path' => '/manga/ajax/series/page/1',

    'expected_status' => 200,

    'fragment' => true,
];