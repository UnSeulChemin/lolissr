<?php

declare(strict_types=1);

// =========================================
// ROUTES INCONNUES
// =========================================

$tests[] = [
    'category' => 'Errors',
    'label' => 'Route inconnue retourne 404',
    'path' => '/route-inexistante',
    'expected_status' => 404
];

// =========================================
// RESSOURCES INEXISTANTES
// =========================================

$notFoundResources = [
    [
        'label' => 'Série inexistante retourne 404',
        'path' => '/manga/series/serie-qui-nexiste-pas'
    ],
    [
        'label' => 'Manga inexistant retourne 404',
        'path' => '/manga/series/serie-qui-nexiste-pas/999'
    ],
    [
        'label' => 'Artbook inexistant retourne 404',
        'path' => '/manga/artbooks/artbook-qui-nexiste-pas/999'
    ],
    [
        'label' => 'Figurine inexistante retourne 404',
        'path' => '/figurine/waifus/waifu-qui-nexiste-pas/999'
    ],
    [
        'label' => 'Nendoroid inexistant retourne 404',
        'path' => '/nendoroid/waifus/waifu-qui-nexiste-pas/999'
    ],
    [
        'label' => 'Peluche inexistante retourne 404',
        'path' => '/peluche/waifus/waifu-qui-nexiste-pas/999'
    ]
];

foreach ($notFoundResources as $resource)
{
    $tests[] = [
        'category' => 'Errors',
        'label' => $resource['label'],
        'path' => $resource['path'],
        'expected_status' => 404
    ];
}

// =========================================
// PAGINATION HORS LIMITE
// =========================================

$tests[] = [
    'category' => 'Errors',
    'label' => 'Page série hors limite retourne 404',
    'path' => '/manga/series/page/999999',
    'expected_status' => 404
];

$tests[] = [
    'category' => 'Errors',
    'label' => 'Page artbooks hors limite retourne 404',
    'path' => '/manga/artbooks/page/999999',
    'expected_status' => 404
];

// =========================================
// PARAMÈTRES INVALIDES
// =========================================

$tests[] = [
    'category' => 'Errors',
    'label' => 'Niveau HSK inexistant retourne 404',
    'path' => '/chinois/grammaire/hsk999',
    'expected_status' => 404
];