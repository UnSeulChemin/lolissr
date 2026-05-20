<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Détection de la page active
|--------------------------------------------------------------------------
*/

$currentPath ??= '/';

/* Base path toujours propre */
$basePath = rtrim(
    (string) ($basePath ?? ''),
    '/',
) . '/';

$cleanBasePath = rtrim($basePath, '/');

/* Retire le basePath si présent */
if (
    $cleanBasePath !== ''
    && $cleanBasePath !== '/'
    && str_starts_with($currentPath, $cleanBasePath)
) {
    $currentPath = substr(
        $currentPath,
        strlen($cleanBasePath),
    );
}

/* Normalise */
$currentPath = $currentPath === ''
    ? '/'
    : $currentPath;

/*
|--------------------------------------------------------------------------
| États actifs
|--------------------------------------------------------------------------
*/

$activeHome = $currentPath === '/'
    ? 'active'
    : '';

$activeManga = (
    $currentPath === '/manga'
    || str_starts_with($currentPath, '/manga/')
)
    ? 'active'
    : '';

$activeChinois = (
    $currentPath === '/chinois'
    || str_starts_with($currentPath, '/chinois/')
)
    ? 'active'
    : '';

/*
|--------------------------------------------------------------------------
| Recherche actuelle
|--------------------------------------------------------------------------
*/

$currentSearch = '';

if (
    str_starts_with(
        $currentPath,
        '/manga/recherche/',
    )
) {
    $searchSlug = substr(
        $currentPath,
        strlen('/manga/recherche/'),
    );

    $currentSearch = str_replace(
        '-',
        ' ',
        urldecode($searchSlug),
    );
}

?>

<header>

    <nav>

        <a
            class="site-logo"
            href="<?= e($basePath) ?>"
            title="Accueil">

            <span class="site-logo-loli">Loli</span>

            <span class="site-logo-ssr">SSR</span>

        </a>

        <ul>

            <li>

                <a
                    class="nav-link-icon <?= e($activeHome) ?>"
                    href="<?= e($basePath) ?>"
                    title="Accueil">

                    🏠

                </a>

            </li>

            <li>

                <a
                    class="nav-link-icon <?= e($activeManga) ?>"
                    href="<?= e($basePath) ?>manga"
                    title="Manga">

                    📚

                </a>

            </li>

            <li>

                <a
                    class="nav-link-icon <?= e($activeChinois) ?>"
                    href="<?= e($basePath) ?>chinois"
                    title="Chinois">

                    ⛩️

                </a>

            </li>

        </ul>

        <div class="header-search-area">

            <form
                class="header-search js-header-search"
                data-base-path="<?= e($basePath) ?>">

                <input
                    id="header-search-input"
                    type="search"
                    name="q"
                    placeholder="Rechercher..."
                    value="<?= e($currentSearch) ?>"
                    aria-label="Rechercher"
                    autocomplete="off">

                <button
                    type="submit"
                    title="Rechercher">

                    🔎

                </button>

            </form>

            <div class="header-search-dropdown js-header-search-dropdown">

                <div
                    class="header-search-skeleton"
                    aria-hidden="true">

                    <?php for ($i = 1; $i <= 5; $i++): ?>

                        <div class="header-search-skeleton-item">

                            <div class="header-search-skeleton-thumb"></div>

                            <div class="header-search-skeleton-texts">

                                <div class="header-search-skeleton-line header-search-skeleton-line-title"></div>

                                <div class="header-search-skeleton-line header-search-skeleton-line-subtitle"></div>

                            </div>

                        </div>

                    <?php endfor; ?>

                </div>

                <div
                    class="header-search-results"
                    id="header-search-results">
                </div>

            </div>

        </div>

    </nav>

</header>