<?php

declare(strict_types=1);

require dirname(__DIR__) . '/phpstan-bootstrap.php';
require ROOT . '/App/Support/Helpers.php';

Framework\Application\Bootstrap::loadEnvOnly();

use App\Support\PageStyles;

$manifest = require ROOT . '/Config/styles.php';

foreach ($manifest as $css => $patterns)
{
    if (!is_file(ROOT . '/public/css/' . $css))
    {
        throw new RuntimeException('Missing CSS: ' . $css);
    }
}

$cases = [
    'pages/auth/connexion' => [],
    'pages/main/index' => ['components/summary.css'],
    'pages/sql/index' => ['components/summary.css', 'page/sql.css'],
    'pages/manga/series/index' => [],
    'pages/manga/series/livre' => ['components/detail.css', 'page/manga/note-rating.css', 'components/status-toggle.css'],
    'pages/manga/artbooks/livre' => ['components/detail.css', 'components/status-toggle.css'],
    'pages/figurine/waifus/waifu' => ['components/detail.css', 'components/status-toggle.css'],
    'pages/nendoroid/waifus/waifu' => ['components/detail.css', 'components/status-toggle.css'],
    'pages/peluche/waifus/waifu' => ['components/detail.css', 'components/status-toggle.css'],
    'pages/chinois/flashcards/grammaire' => ['page/chinois/vocabulaire.css', 'page/chinois/grammaire.css'],
    'pages/profile/personnalisation' => [
        'base/profile-title-modal.css', 'components/media-picker.css',
        'components/summary.css', 'components/profile-avatar.css', 'page/profil/profil.css', 'page/profil/personnalisation.css',
    ],
    'errors/404' => [],
];

foreach ($cases as $view => $expected)
{
    $actual = PageStyles::forView(view_path($view . '.php'));
    $urls = array_map(static fn ($file) => view_base_uri() . 'css/' . $file, $expected);

    if ($actual !== $urls)
    {
        throw new RuntimeException('Wrong dependencies: ' . $view);
    }

    echo 'PASS: ' . $view . PHP_EOL;
}
