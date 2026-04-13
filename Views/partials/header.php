<?php
$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';
$cleanBasePath = rtrim($basePath, '/');

$relativePath = $currentPath;

if (
    $cleanBasePath !== ''
    && $cleanBasePath !== '/'
    && str_starts_with($currentPath, $cleanBasePath)
) {
    $relativePath = substr($currentPath, strlen($cleanBasePath));
}

$relativePath = $relativePath === '' ? '/' : $relativePath;

$activeHome = $relativePath === '/' ? 'active' : '';
$activeManga = str_starts_with($relativePath, '/manga') ? 'active' : '';
?>

<header>
    <nav class="flex-center-center">

        <ul class="flex-gap-50">
            <li>
                <a class="link-menu <?= $activeHome; ?>"
                   href="<?= $basePath; ?>">
                   Accueil
                </a>
            </li>

            <li>
                <a class="link-menu <?= $activeManga; ?>"
                   href="<?= $basePath; ?>manga">
                   Manga
                </a>
            </li>
        </ul>

    </nav>
</header>