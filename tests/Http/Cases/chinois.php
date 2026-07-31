<?php

declare(strict_types=1);

// =========================================
// PAGES
// =========================================

$tests[] = [
    'category' => 'Chinois',
    'label' => 'Accueil chinois accessible',
    'path' => '/chinois'
];

$tests[] = [
    'category' => 'Chinois',
    'label' => 'Page vocabulaire accessible',
    'path' => '/chinois/vocabulaire'
];

$tests[] = [
    'category' => 'Chinois',
    'label' => 'Page vocabulaire mandarin accessible',
    'path' => '/chinois/vocabulaire/mandarin'
];

$tests[] = [
    'category' => 'Chinois',
    'label' => 'Page vocabulaire jinyu accessible',
    'path' => '/chinois/vocabulaire/jinyu'
];

$tests[] = [
    'category' => 'Chinois',
    'label' => 'Page grammaire accessible',
    'path' => '/chinois/grammaire'
];

// =========================================
// HSK
// =========================================

foreach ([1, 2, 3, 4] as $level)
{
    $tests[] = [
        'category' => 'Chinois',
        'label' => "Page HSK {$level} accessible",
        'path' => "/chinois/grammaire/hsk{$level}"
    ];
}

// =========================================
// FLASHCARDS
// =========================================

$tests[] = [
    'category' => 'Chinois',
    'label' => 'Page flashcards accessible',
    'path' => '/chinois/flashcards'
];

$tests[] = [
    'category' => 'Chinois',
    'label' => 'Flashcards vocabulaire accessible',
    'path' => '/chinois/flashcards/vocabulaire'
];

$tests[] = [
    'category' => 'Chinois',
    'label' => 'Flashcards grammaire accessible',
    'path' => '/chinois/flashcards/grammaire'
];