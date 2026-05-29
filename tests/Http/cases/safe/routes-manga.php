<?php

declare(strict_types=1);

$tests[] = [

    'category' => 'Manga',

    'label' => 'Index',

    'path' => '/manga',

    'contains' => [
        'Manga',
    ],
];

$tests[] = [

    'category' => 'Manga',

    'label' => 'Recherche',

    'path' => '/manga/recherche',

    'contains' => [
        'Recherche',
    ],
];

$tests[] = [

    'category' => 'Manga',

    'label' => 'Series',

    'path' => '/manga/series',
];

$tests[] = [

    'category' => 'Manga',

    'label' => 'Series Page 1',

    'path' => '/manga/series/page/1',
];

$tests[] = [

    'category' => 'Manga',

    'label' => 'Manga Detail',

    'path' => '/manga/series/i-want-to-see-you-shy/1',

    'contains' => [
        'I Want To See You Shy',
    ],
];