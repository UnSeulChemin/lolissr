<?php

declare(strict_types=1);

$tests[] = [

    'category' => 'Manga',

    'label' => 'Page manga accessible',

    'path' => '/manga',
];

$tests[] = [

    'category' => 'Manga',

    'label' => 'Recherche manga accessible',

    'path' => '/manga/recherche',
];

$tests[] = [

    'category' => 'Manga',

    'label' => 'Liste des séries accessible',

    'path' => '/manga/series',
];

$tests[] = [

    'category' => 'Manga',

    'label' => 'Pagination séries page 1 accessible',

    'path' => '/manga/series/page/1',
];

$tests[] = [

    'category' => 'Manga',

    'label' => 'Fiche manga existante accessible',

    'path' => '/manga/series/i-want-to-see-you-shy/1',
];