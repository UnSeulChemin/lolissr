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
$currentSearch = $_GET['q'] ?? '';

?>

<header>
    <nav>

        <!-- LOGO -->
        <a
            class="site-logo"
            href="<?= $basePath ?>"
            title="Accueil">

            <span class="site-logo-loli">Loli</span>
            <span class="site-logo-ssr">SSR</span>

        </a>

        <!-- MENU CENTRÉ -->
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

        <!-- RECHERCHE -->
        <form
            class="header-search"
            action="<?= $basePath ?>manga/recherche"
            method="get">

            <input
                type="search"
                name="q"
                placeholder="Rechercher un manga..."
                value="<?= htmlspecialchars($currentSearch) ?>"
                aria-label="Rechercher un manga">

            <button
                type="submit"
                title="Rechercher">

                🔎

            </button>

        </form>

    </nav>
</header>