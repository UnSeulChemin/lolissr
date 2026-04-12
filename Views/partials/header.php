<?php
$currentPage = $_GET['p'] ?? '';
$activeHome = empty($currentPage) ? 'active' : '';
$activeManga = str_contains($currentPage, 'manga') ? 'active' : '';
?>

<header>
    <nav class="flex-center-center">

        <ul class="flex-gap-50">
            <li>
                <a class="<?= $activeHome ?> link-menu"
                   href="<?= $basePath; ?>">
                   Accueil
                </a>
            </li>

            <li>
                <a class="<?= $activeManga ?> link-menu"
                   href="<?= $basePath; ?>manga">
                   Manga
                </a>
            </li>
        </ul>

    </nav>
</header>