<?php

declare(strict_types=1);

// Dépendances ordonnées. Une barre finale sélectionne toutes les vues du dossier.
// Les chemins sont relatifs à App/Views, sans extension .php.
return [
    'pages/admin/index.css' => ['pages/admin/'],
    'pages/profile/achievements.css' => ['pages/profile/achievements'],
    'components/modals/profile-title-modal.css' => ['pages/profile/'],
    'components/media-picker.css' => ['pages/profile/'],
    'components/summary.css' => [
        'pages/home/index',
        'pages/sql/index',
        'pages/profile/',
        'pages/manga/series/notes',
        'pages/manga/series/unread', 'pages/manga/series/releases'
    ],
    'pages/sql/index.css' => ['pages/sql/'],
    // Le vocabulaire et la grammaire partagent les styles des boutons et de navigation des cartes.
    'pages/chinois/vocabulary.css' => ['pages/chinois/'],
    'pages/chinois/grammar.css' => ['pages/chinois/'],
    'components/profile-avatar.css' => ['pages/profile/'],
    'pages/profile/index.css' => ['pages/profile/'],
    'pages/profile/customization.css' => ['pages/profile/customization'],
    'components/detail.css' => [
        'errors/',
        'pages/manga/series/show',
        'pages/artbook/show',
        'pages/figurine/collection/show',
        'pages/nendoroid/collection/show',
        'pages/peluche/collection/show'
    ],
    'pages/manga/note-rating.css' => ['pages/manga/series/show'],
    'components/status-toggle.css' => [
        'pages/manga/series/show',
        'pages/artbook/show',
        'pages/figurine/collection/show',
        'pages/nendoroid/collection/show',
        'pages/peluche/collection/show'
    ]
];
