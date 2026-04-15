<?php

/*
|--------------------------------------------------------------------------
| Détection de la page active
|--------------------------------------------------------------------------
*/

$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';
$cleanBasePath = rtrim($basePath, '/');

/* Retire le basePath si présent */
if ($cleanBasePath !== '' && $cleanBasePath !== '/' && str_starts_with($currentPath, $cleanBasePath))
{
    $currentPath = substr($currentPath, strlen($cleanBasePath));
}

/* Normalise */
$currentPath = $currentPath === '' ? '/' : $currentPath;

/* États actifs */
$activeHome = $currentPath === '/' ? 'active' : '';
$activeManga = ($currentPath === '/manga' || str_starts_with($currentPath, '/manga/')) ? 'active' : '';

?>

<header>
    <nav>

        <a class="site-logo"
            href="<?= $basePath ?>"
            title="Accueil">

            <span class="site-logo-loli">Loli</span>
            <span class="site-logo-ssr">SSR</span>

        </a>

        <ul>
            <li><a class="nav-link-icon <?= $activeHome ?>" href="<?= $basePath ?>" title="Accueil">🏠</a></li>
            <li><a class="nav-link-icon <?= $activeManga ?>" href="<?= $basePath ?>manga" title="Manga">📚</a></li>
        </ul>

    </nav>
</header>