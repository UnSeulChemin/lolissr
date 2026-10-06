<?php

declare(strict_types=1);

// =========================================
// PAGES
// =========================================

$tests[] = ['category' => 'Main', 'label' => 'Accueil accessible', 'path' => '/'];

$sqlUser = (new \App\Repositories\Auth\UserRepository(new \Framework\Database\Database()))
    ->findByUsername((string) env('HTTP_TEST_USERNAME'));
foreach (['/admin/commandes/sorties', '/admin/commandes/sorties/mon-compte'] as $releasePath)
    $tests[] = ['category' => 'Main', 'label' => 'Sorties réservées au compte 1 et protégées par CSRF', 'method' => 'POST', 'path' => $releasePath,
        'expected_status' => $sqlUser !== null && $sqlUser->id === 1 ? 419 : 404];
$tests[] = ['category' => 'Main', 'label' => 'Outil SQL réservé au compte 1', 'path' => '/admin/sql',
    'expected_status' => $sqlUser !== null && $sqlUser->id === 1 && env_bool('SQL_TOOL_ENABLED', true) ? 200 : 404];
$tests[] = ['category' => 'Main', 'label' => 'Administration réservée au compte 1', 'path' => '/admin',
    'expected_status' => $sqlUser !== null && $sqlUser->id === 1 ? 200 : 404];
$tests[] = ['category' => 'Main', 'label' => 'Commandes réservées au compte 1', 'path' => '/admin/commandes',
    'expected_status' => $sqlUser !== null && $sqlUser->id === 1 ? 200 : 404];
$tests[] = ['category' => 'Main', 'label' => 'Actualisation exige CSRF', 'method' => 'POST', 'path' => '/admin/commandes/recommandations',
    'expected_status' => $sqlUser !== null && $sqlUser->id === 1 ? 419 : 404];
$tests[] = ['category' => 'Main', 'label' => 'Actualisation personnelle exige CSRF', 'method' => 'POST', 'path' => '/admin/commandes/recommandations/mon-compte',
    'expected_status' => $sqlUser !== null && $sqlUser->id === 1 ? 419 : 404];

// =========================================
// MÉTHODES HTTP
// =========================================

$tests[] = [
    'category' => 'Main',
    'label' => 'Accueil refuse POST',
    'method' => 'POST',
    'path' => '/',
    'expected_status' => 405,
    'header_contains' => ['Allow: GET']
];
