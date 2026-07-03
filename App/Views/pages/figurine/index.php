<?php

declare(strict_types=1);

$baseUri = view_base_uri();

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
                🎀 Figurines
            </h1>

            <p class="dashboard-description">
                Gère ta collection de figurines, ajoute de nouvelles pièces et consulte tes vitrines.
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
            href="<?= e($baseUri) ?>figurine/waifus"
        >

            <span
                class="dashboard-card-icon"
                aria-hidden="true"
            >
                🎀
            </span>

            <span class="dashboard-card-title">
                Waifus
            </span>

            <span class="dashboard-card-description">
                Voir toutes les figurines enregistrées.
            </span>

        </a>

        <a
            class="
                card
                transition-card
                dashboard-card
            "
            data-prefetch
            href="<?= e($baseUri) ?>figurine/ajouter"
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
                Ajouter une nouvelle figurine à la collection.
            </span>

        </a>

        <a
            class="
                card
                transition-card
                dashboard-card
            "
            data-prefetch
            href="<?= e($baseUri) ?>figurine/lien"
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
                Sites, boutiques et ressources utiles autour des figurines.
            </span>

        </a>

    </section>

</section>