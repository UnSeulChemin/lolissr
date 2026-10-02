<?php

declare(strict_types=1);

// Ordered dependencies. A trailing slash matches every view in that directory.
// Paths are relative to App/Views, without the .php extension.
return [
    'pages/profile/achievements.css' => ['pages/profile/achievements'],
    'components/modals/profile-title-modal.css' => ['pages/profile/'],
    'components/media-picker.css' => ['pages/profile/'],
    'components/summary.css' => [
        'pages/home/index',
        'pages/sql/index',
        'pages/profile/',
        'pages/manga/series/notes',
        'pages/manga/series/unread',
    ],
    'pages/sql.css' => ['pages/sql/'],
    // Vocabulary and grammar share buttons and flashcard navigation styles.
    'pages/chinois/vocabulaire.css' => ['pages/chinois/'],
    'pages/chinois/grammaire.css' => ['pages/chinois/'],
    'components/profile-avatar.css' => ['pages/profile/'],
    'pages/profile/profile.css' => ['pages/profile/'],
    'pages/profile/customization.css' => ['pages/profile/customization'],
    'components/detail.css' => [
        'pages/manga/series/show',
        'pages/artbook/show',
        'pages/figurine/collection/show',
        'pages/nendoroid/collection/show',
        'pages/peluche/collection/show',
    ],
    'pages/manga/note-rating.css' => ['pages/manga/series/show'],
    'components/status-toggle.css' => [
        'pages/manga/series/show',
        'pages/artbook/show',
        'pages/figurine/collection/show',
        'pages/nendoroid/collection/show',
        'pages/peluche/collection/show',
    ],
];
