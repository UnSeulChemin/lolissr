<?php

declare(strict_types=1);

require dirname(__DIR__, 2) . '/tests/Support/bootstrap.php';
require ROOT . '/App/Support/Helpers.php';

Framework\Application\Bootstrap::loadEnvOnly();

use App\Support\Assets\PageStyles;

$manifest = require ROOT . '/Config/styles.php';

foreach ($manifest as $css => $patterns)
{
    if (!is_file(ROOT . '/public/css/' . $css))
    {
        throw new RuntimeException('Missing CSS: ' . $css);
    }
}

$cases = [
    'pages/auth/login' => [],
    'pages/home/index' => ['components/summary.css'],
    'pages/sql/index' => ['components/summary.css', 'pages/sql.css'],
    'pages/manga/series/index' => [],
    'pages/manga/series/show' => ['components/detail.css', 'pages/manga/note-rating.css', 'components/status-toggle.css'],
    'pages/artbook/show' => ['components/detail.css', 'components/status-toggle.css'],
    'pages/figurine/collection/show' => ['components/detail.css', 'components/status-toggle.css'],
    'pages/nendoroid/collection/show' => ['components/detail.css', 'components/status-toggle.css'],
    'pages/peluche/collection/show' => ['components/detail.css', 'components/status-toggle.css'],
    'pages/chinois/flashcards/grammaire' => ['pages/chinois/vocabulaire.css', 'pages/chinois/grammaire.css'],
    'pages/profile/customization' => [
        'components/modals/profile-title-modal.css', 'components/media-picker.css',
        'components/summary.css', 'components/profile-avatar.css', 'pages/profile/profile.css', 'pages/profile/customization.css',
    ],
    'errors/404' => [],
];

foreach ($cases as $view => $expected)
{
    $actual = PageStyles::forView(view_path($view . '.php'));
    $urls = array_map(static fn ($file) => view_base_uri() . 'css/' . $file
        . '?v=' . hash_file('sha256', ROOT . '/public/css/' . $file), $expected);

    if ($actual !== $urls)
    {
        throw new RuntimeException('Wrong dependencies: ' . $view);
    }

    echo 'PASS: ' . $view . PHP_EOL;
}
