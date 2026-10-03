<?php

declare(strict_types=1);

// =========================================
// PAGES
// =========================================

$tests[] = [
    'category' => 'Figurine',
    'label' => 'Accueil figurines',
    'path' => '/figurine'
];

$tests[] = [
    'category' => 'Figurine',
    'label' => 'Liens figurines',
    'path' => '/figurine/lien'
];

$tests[] = [
    'category' => 'Figurine',
    'label' => 'Liste des figurines',
    'path' => '/figurine/figurines'
];

$tests[] = [
    'category' => 'Figurine',
    'label' => 'Pagination figurines page 1',
    'path' => '/figurine/figurines/page/1'
];

$tests[] = [
    'category' => 'Figurine',
    'label' => 'Ajout figurine',
    'path' => '/figurine/ajouter'
];

// =========================================
// AJAX HTML
// =========================================

$tests[] = [
    'category' => 'Figurine',
    'label' => 'Pagination figurines HTML',
    'path' => '/figurine/ajax/figurines/page/1',
    'fragment' => true
];

// =========================================
// AJAX JSON
// =========================================

$tests[] = [
    'category' => 'Figurine',
    'label' => 'Recherche JSON',
    'path' => '/figurine/ajax/recherche/test',
    'json' => true,
    'header_contains' => [
        'application/json',
    ],
    'headers' => [
        'Accept: application/json',
        'X-Requested-With: XMLHttpRequest',
    ],
];