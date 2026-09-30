<?php

declare(strict_types=1);

// =========================================
// PROFIL
// =========================================

$tests[] = [
    'category' => 'Auth',
    'label' => 'Profil accessible',
    'path' => '/profil'
];

$tests[] = [
    'category' => 'Auth',
    'label' => 'Personnalisation du profil accessible',
    'path' => '/profil/personnalisation'
];

// =========================================
// SUCCÈS ET CATALOGUES
// =========================================

foreach (['/profil/xp', '/profil/succes'] as $path)
{
    $tests[] = ['category' => 'Auth', 'label' => 'Lecture du profil : ' . $path, 'path' => $path];
}

foreach (['titles', 'avatars', 'banners', 'frames'] as $catalog)
{
    $tests[] = [
        'category' => 'Auth',
        'label' => 'Catalogue du profil : ' . $catalog,
        'path' => '/profil/ajax/' . $catalog,
        'json' => true,
        'header_contains' => ['application/json'],
        'headers' => ['Accept: application/json'],
    ];
}

// =========================================
// MÉTHODES HTTP
// =========================================

$tests[] = [
    'category' => 'Auth',
    'label' => 'Déconnexion refuse GET',
    'method' => 'GET',
    'path' => '/deconnexion',
    'expected_status' => 405,
    'header_contains' => ['Allow: POST']
];

$tests[] = [
    'category' => 'Auth',
    'label' => 'Connexion refuse PUT',
    'method' => 'PUT',
    'path' => '/connexion',
    'expected_status' => 405,
    'header_contains' => ['Allow: GET, POST']
];
