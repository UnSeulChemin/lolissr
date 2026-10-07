<?php

declare(strict_types=1);

require dirname(__DIR__, 3) . '/tests/Support/bootstrap.php';
require ROOT . '/App/Support/Helpers.php';

Framework\Application\Bootstrap::loadEnvOnly();

use App\Support\Assets\PageStyles;

$manifest = require ROOT . '/Config/assets/page-styles.php';

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
    'pages/admin/index' => ['pages/admin/index.css'],
    'pages/manga/series/index' => [],
    'pages/manga/series/show' => ['components/detail.css', 'pages/manga/note-rating.css', 'components/status-toggle.css'],
    'pages/artbook/show' => ['components/detail.css', 'components/status-toggle.css'],
    'pages/figurine/collection/show' => ['components/detail.css', 'components/status-toggle.css'],
    'pages/nendoroid/collection/show' => ['components/detail.css', 'components/status-toggle.css'],
    'pages/peluche/collection/show' => ['components/detail.css', 'components/status-toggle.css'],
    'pages/chinois/flashcards/grammaire' => ['pages/chinois/vocabulary.css', 'pages/chinois/grammar.css'],
    'pages/profile/customization' => [
        'components/modals/profile-title-modal.css', 'components/media-picker.css',
        'components/summary.css', 'components/profile-avatar.css', 'pages/profile/index.css', 'pages/profile/customization.css'
    ],
    'errors/403' => ['components/detail.css'],
    'errors/404' => ['components/detail.css'],
    'errors/405' => ['components/detail.css'],
    'errors/419' => ['components/detail.css'],
    'errors/422' => ['components/detail.css'],
    'errors/500' => ['components/detail.css']
];

foreach ($cases as $view => $expected)
{
    if (!is_file(view_path($view . '.php')))
    {
        throw new RuntimeException('Missing view: ' . $view);
    }
    $actual = PageStyles::forView(view_path($view . '.php'));
    $urls = array_map(static fn ($file) => view_base_uri() . 'css/' . $file
        . '?v=' . hash_file('sha256', ROOT . '/public/css/' . $file), $expected);

    if ($actual !== $urls)
    {
        throw new RuntimeException('Wrong dependencies: ' . $view);
    }

    echo 'PASS: ' . $view . PHP_EOL;
}
