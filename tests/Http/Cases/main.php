<?php

declare(strict_types=1);

// =========================================
// PAGES
// =========================================

$tests[] = ['category' => 'Main', 'label' => 'Accueil accessible', 'path' => '/'];

$sqlUser = (new \App\Repositories\Auth\UserRepository(new \Framework\Database\Database()))
    ->findByUsername((string) env('HTTP_TEST_USERNAME'));
$tests[] = ['category' => 'Main', 'label' => 'Outil SQL réservé aux administrateurs', 'path' => '/sql',
    'expected_status' => $sqlUser !== null && $sqlUser->is_admin ? 200 : 404];

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
