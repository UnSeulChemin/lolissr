<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| PAGES PRINCIPALES
|--------------------------------------------------------------------------
*/

$tests[] = [
    'category' => 'Main',
    'label' => 'Accueil accessible',
    'path' => '/',
];

$tests[] = [
    'category' => 'Main',
    'label' => 'Outil SQL accessible',
    'path' => '/sql',
];

/*
|--------------------------------------------------------------------------
| MÉTHODES HTTP
|--------------------------------------------------------------------------
*/

$tests[] = [
    'category' => 'Main',
    'label' => 'Accueil refuse POST',
    'method' => 'POST',
    'path' => '/',
    'expected_status' => 405,
    'header_contains' => [
        'Allow: GET',
    ],
];