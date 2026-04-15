<?php

/*
|--------------------------------------------------------------------------
| Détection de la page active
|--------------------------------------------------------------------------
*/

$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';
$cleanBasePath = rtrim($basePath, '/');

/* Retire le basePath si présent */
if (
    $cleanBasePath !== ''
    && $cleanBasePath !== '/'
    && str_starts_with($currentPath, $cleanBasePath)
)
{
    $currentPath = substr($currentPath, strlen($cleanBasePath));
}

/* Normalise */
$currentPath = $currentPath === '' ? '/' : $currentPath;

/* États actifs */
$activeHome = $currentPath === '/' ? 'active' : '';

$activeManga = (
    $currentPath === '/manga'
    || str_starts_with($currentPath, '/manga/')
)
    ? 'active'
    : '';

/* Garde la recherche si présente */
$currentSearch = '';

if (str_starts_with($currentPath, '/manga/recherche/'))
{
    $searchSlug = substr($currentPath, strlen('/manga/recherche/'));
    $currentSearch = str_replace('-', ' ', urldecode($searchSlug));
}

?>

<header>
    <nav>

        <a
            class="site-logo"
            href="<?= $basePath ?>"
            title="Accueil">

            <span class="site-logo-loli">Loli</span>
            <span class="site-logo-ssr">SSR</span>

        </a>

        <ul>
            <li>
                <a
                    class="nav-link-icon <?= $activeHome ?>"
                    href="<?= $basePath ?>"
                    title="Accueil">
                    🏠
                </a>
            </li>

            <li>
                <a
                    class="nav-link-icon <?= $activeManga ?>"
                    href="<?= $basePath ?>manga"
                    title="Manga">
                    📚
                </a>
            </li>
        </ul>

        <div class="header-search-area">

            <form
                class="header-search js-header-search"
                data-base-path="<?= htmlspecialchars($basePath, ENT_QUOTES) ?>">

                <input
                    id="header-search-input"
                    type="search"
                    name="q"
                    placeholder="Rechercher un manga..."
                    value="<?= htmlspecialchars($currentSearch) ?>"
                    aria-label="Rechercher un manga"
                    autocomplete="off">

                <button
                    type="submit"
                    title="Rechercher">
                    🔎
                </button>

            </form>

            <div class="header-search-dropdown js-header-search-dropdown">

                <div class="header-search-skeleton" aria-hidden="true">
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