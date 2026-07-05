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
            "
        >

            <h1 class="dashboard-title">
                📚 Manga
            </h1>

            <p class="dashboard-description">
                Gère ta collection de mangas et d'artbooks.
            </p>

        </div>

    </section>

    <section class="dashboard-grid">

        <a
            class="
                card
                transition-card
                dashboard-card
            "
            data-prefetch
            href="<?= e($view->baseUri) ?>manga/series"
        >

            <span
                class="dashboard-card-icon"
                aria-hidden="true"
            >
                📚
            </span>

            <span class="dashboard-card-title">
                Séries
            </span>

            <span class="dashboard-card-description">
                Parcourir toutes les séries de mangas.
            </span>

        </a>

        <a
            class="
                card
                transition-card
                dashboard-card
            "
            data-prefetch
            href="<?= e($view->baseUri) ?>manga/artbooks"
        >

            <span
                class="dashboard-card-icon"
                aria-hidden="true"
            >
                🎨
            </span>

            <span class="dashboard-card-title">
                Artbooks
            </span>

            <span class="dashboard-card-description">
                Parcourir tous les artbooks de la collection.
            </span>

        </a>

        <a
            class="
                card
                transition-card
                dashboard-card
            "
            data-prefetch
            href="<?= e($view->baseUri) ?>manga/ajouter"
        >

            <span
                class="dashboard-card-icon"
                aria-hidden="true"
            >
                ➕
            </span>

            <span class="dashboard-card-title">
                Ajouter
            </span>

            <span class="dashboard-card-description">
                Ajouter un manga ou un artbook à la collection.
            </span>

        </a>

        <a
            class="
                card
                transition-card
                dashboard-card
            "
            data-prefetch
            href="<?= e($view->baseUri) ?>manga/lien"
        >

            <span
                class="dashboard-card-icon"
                aria-hidden="true"
            >
                🔗
            </span>

            <span class="dashboard-card-title">
                Liens utiles
            </span>

            <span class="dashboard-card-description">
                Sites, références et ressources utiles autour des mangas.
            </span>

        </a>

    </section>

</section>