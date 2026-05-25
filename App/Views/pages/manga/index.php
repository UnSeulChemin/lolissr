<?php

declare(strict_types=1);

$baseUri =
    rtrim(
        (string) ($baseUri ?? ''),
        '/',
    ) . '/';

?>

<section class="layout-container dashboard-page">

    <section class="dashboard-header">

        <div
            class="
                dashboard-title-box
                transition-card
            "
        >

            <h1 class="dashboard-title">
                📚 Manga
            </h1>

            <p class="dashboard-description">
                Gère ta collection, ajoute des mangas et accède à tes liens utiles.
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
            href="<?= e($baseUri) ?>manga/series"
        >

            <span
                class="dashboard-card-icon"
                aria-hidden="true"
            >
                📚
            </span>

            <span class="dashboard-card-title">
                Series
            </span>

            <span class="dashboard-card-description">
                Voir tous les mangas enregistrés.
            </span>

        </a>

        <a
            class="
                card
                transition-card
                dashboard-card
            "
            href="<?= e($baseUri) ?>manga/ajouter"
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
                Ajouter un nouveau manga à la collection.
            </span>

        </a>

        <a
            class="
                card
                transition-card
                dashboard-card
            "
            href="<?= e($baseUri) ?>manga/lien"
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