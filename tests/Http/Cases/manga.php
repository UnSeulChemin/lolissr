<?php

declare(strict_types=1);

$tests[] = [

    'category' => 'Manga',

    'label' => 'Accueil manga',

    'path' => '/manga',
];

$tests[] = [

    'category' => 'Manga',

    'label' => 'Recherche manga',

    'path' => '/manga/recherche',
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

    'label' => 'Fiche manga existante',

    'path' => '/manga/series/i-want-to-see-you-shy/1',
];