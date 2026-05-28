<?php

declare(strict_types=1);

$tests[] = [
    'category' => 'Pages',
    'label' => 'Accueil',
    'path' => '/',
    'expected_status' => 200,
];

$tests[] = [
    'category' => 'Manga',
    'label' => 'Index',
    'path' => '/manga',
    'expected_status' => 200,
];

$tests[] = [
    'category' => 'Manga Recherche',
    'label' => 'Recherche',
    'path' => '/manga/recherche',
    'expected_status' => 200,
];

$tests[] = [
    'category' => 'Manga Series',
    'label' => 'Series',
    'path' => '/manga/series',
    'expected_status' => 200,
];

$tests[] = [
    'category' => 'Manga Series',
    'label' => 'Page 1',
    'path' => '/manga/series/page/1',
    'expected_status' => 200,
];

$tests[] = [
    'category' => 'Manga Detail',
    'label' => 'I Want To See You Shy',
    'path' => '/manga/series/i-want-to-see-you-shy/1',
    'expected_status' => 200,
];

$tests[] = [
    'category' => 'Chinois',
    'label' => 'Accueil',
    'path' => '/chinois',
    'expected_status' => 200,
];

$tests[] = [
    'category' => 'Chinois',
    'label' => 'Mandarin',
    'path' => '/chinois/mandarin',
    'expected_status' => 200,
];

$tests[] = [
    'category' => 'Chinois',
    'label' => 'Jinyu',
    'path' => '/chinois/jinyu',
    'expected_status' => 200,
];

$tests[] = [
    'category' => 'Chinois',
    'label' => 'Grammaire',
    'path' => '/chinois/grammaire',
    'expected_status' => 200,
];

$tests[] = [
    'category' => 'Chinois',
    'label' => 'HSK1',
    'path' => '/chinois/grammaire/hsk1',
    'expected_status' => 200,
];

$tests[] = [
    'category' => 'Chinois',
    'label' => 'Flashcards',
    'path' => '/chinois/flashcards',
    'expected_status' => 200,
];