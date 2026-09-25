<?php

declare(strict_types=1);

use App\DTO\Common\Responses\ViewData;

/** @var ViewData $view */

?>

<section class="layout-container dashboard-page">

    <section class="dashboard-header">

        <div
            class="
                dashboard-title-box
                transition-title
             u-relative u-text-center u-clip u-border-box"
        >

            <h1 class="dashboard-title u-relative u-bold">
                📚 Manga
            </h1>

            <p class="dashboard-description u-relative">
                Gère ta collection de mangas et d'artbooks.
            </p>

        </div>

    </section>

    <section class="dashboard-grid u-grid u-justify-center">

        <a
            class="
                card
                transition-card
                dashboard-card
             u-stack u-relative u-clip u-border-box"
            data-prefetch
            href="<?= e($view->baseUri) ?>manga/series"
        >

            <span
                class="dashboard-card-icon u-row-center"
                aria-hidden="true"
            >
                📚
            </span>

            <span class="dashboard-card-title u-relative u-w-full u-bold">
                Séries
            </span>

            <span class="dashboard-card-description u-relative u-w-full">
                Parcourir toutes les séries de mangas.
            </span>

        </a>

        <a
            class="
                card
                transition-card
                dashboard-card
             u-stack u-relative u-clip u-border-box"
            data-prefetch
            href="<?= e($view->baseUri) ?>manga/artbooks"
        >

            <span
                class="dashboard-card-icon u-row-center"
                aria-hidden="true"
            >
                📕
            </span>

            <span class="dashboard-card-title u-relative u-w-full u-bold">
                Artbooks
            </span>

            <span class="dashboard-card-description u-relative u-w-full">
                Parcourir tous les artbooks de la collection.
            </span>

        </a>

        <a
            class="
                card
                transition-card
                dashboard-card
             u-stack u-relative u-clip u-border-box"
            data-prefetch
            href="<?= e($view->baseUri) ?>manga/ajouter"
        >

            <span
                class="dashboard-card-icon u-row-center"
                aria-hidden="true"
            >
                ➕
            </span>

            <span class="dashboard-card-title u-relative u-w-full u-bold">
                Ajouter
            </span>

            <span class="dashboard-card-description u-relative u-w-full">
                Ajouter un manga ou un artbook à la collection.
            </span>

        </a>

        <a
            class="
                card
                transition-card
                dashboard-card
             u-stack u-relative u-clip u-border-box"
            data-prefetch
            href="<?= e($view->baseUri) ?>manga/lien"
        >

            <span
                class="dashboard-card-icon u-row-center"
                aria-hidden="true"
            >
                🔗
            </span>

            <span class="dashboard-card-title u-relative u-w-full u-bold">
                Liens utiles
            </span>

            <span class="dashboard-card-description u-relative u-w-full">
                Sites, références et ressources utiles autour des mangas.
            </span>

        </a>

    </section>

</section>