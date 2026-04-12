<?php
$currentPage = $_GET['p'] ?? '';
$activeHome = empty($currentPage) ? 'active' : '';
$activeFigurine = str_contains($currentPage, 'figurine') ? 'active' : '';
$activeNendoroid = str_contains($currentPage, 'nendoroid') ? 'active' : '';
$activePlush = str_contains($currentPage, 'plush') ? 'active' : '';
$activeManga = str_contains($currentPage, 'manga') ? 'active' : '';
$activeChinese = str_contains($currentPage, 'chinese') ? 'active' : '';
?>

<header>
    <nav class="flex-center-center">

        <ul class="flex-gap-50">
            <!-- <li><a class="<?= $activeHome ?> link-menu" href="<?= $basePath; ?>">Home</a></li>
            <li><a class="<?= $activeFigurine ?> link-menu" href="<?= $basePath; ?>figurine">Figurine</a></li>
            <li><a class="<?= $activeNendoroid ?> link-menu" href="<?= $basePath; ?>nendoroid">Nendoroid</a></li>
            <li><a class="<?= $activePlush ?> link-menu" href="<?= $basePath; ?>plush">Plush</a></li> -->
            <li><a class="<?= $activeManga ?> link-menu" href="<?= $basePath; ?>manga">Manga</a></li>
            <li><a class="<?= $activeChinese ?> link-menu" href="<?= $basePath; ?>chinese">Chinese</a></li>
        </ul>

    </nav>
</header>