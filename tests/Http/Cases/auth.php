<?php

declare(strict_types=1);

$tests[] = [

    'category' => 'Auth',

    'label' => 'Profil accessible',

    'path' => '/profil',
];

$tests[] = [

    'category' => 'Auth',

    'label' => 'Personnalisation du profil accessible',

    'path' => '/profil/personnalisation',
];

$tests[] = [

    'category' => 'Auth',

    'label' => 'Déconnexion refuse GET',

    'method' => 'GET',

    'path' => '/deconnexion',

    'expected_status' => 405,

    'header_contains' => [
        'Allow: POST',
    ],
];

$tests[] = [

    'category' => 'Auth',

    'label' => 'Connexion refuse PUT',

    'method' => 'PUT',

    'path' => '/connexion',

    'expected_status' => 405,

    'header_contains' => [
        'Allow: GET, POST',
    ],
];