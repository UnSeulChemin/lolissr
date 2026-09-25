<?php

declare(strict_types=1);

// Ordered dependencies. A trailing slash matches every view in that directory.
// Paths are relative to App/Views, without the .php extension.
return [
    'base/profile-title-modal.css' => ['pages/profile/'],
    'components/media-picker.css' => ['pages/profile/'],
    'components/summary.css' => [
        'pages/main/index',
        'pages/sql/index',
        'pages/profile/',
        'pages/manga/series/notes',
        'pages/manga/series/a-lire',
    ],
    'page/sql.css' => ['pages/sql/'],
    // Vocabulary and grammar share buttons and flashcard navigation styles.
    'page/chinois/vocabulaire.css' => ['pages/chinois/'],
    'page/chinois/grammaire.css' => ['pages/chinois/'],
    'components/profile-avatar.css' => ['pages/profile/'],
    'page/profil/profil.css' => ['pages/profile/'],
    'page/profil/personnalisation.css' => ['pages/profile/personnalisation'],
    'components/detail.css' => [
        'pages/manga/series/livre',
        'pages/manga/artbooks/livre',
        'pages/figurine/waifus/waifu',
        'pages/nendoroid/waifus/waifu',
        'pages/peluche/waifus/waifu',
    ],
    'page/manga/note-rating.css' => ['pages/manga/series/livre'],
    'components/status-toggle.css' => [
        'pages/manga/series/livre',
        'pages/manga/artbooks/livre',
        'pages/figurine/waifus/waifu',
        'pages/nendoroid/waifus/waifu',
        'pages/peluche/waifus/waifu',
    ],
];
