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
                transition-title
            "
        >

            <h1 class="dashboard-title">
                🧸 Peluches
            </h1>

            <p class="dashboard-description">
                Gère ta collection de peluches, ajoute de nouveaux modèles et consulte ta collection.
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
            href="<?= e($baseUri) ?>peluches/waifus"
        >

            <span
                class="dashboard-card-icon"
                aria-hidden="true"
            >
                🧸
            </span>

            <span class="dashboard-card-title">
                Waifus
            </span>

            <span class="dashboard-card-description">
                Voir toutes les peluches enregistrées.
            </span>

        </a>

        <a
            class="
                card
                transition-card
                dashboard-card
            "
            data-prefetch
            href="<?= e($baseUri) ?>peluches/ajouter"
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
                Ajouter une nouvelle peluche à la collection.
            </span>

        </a>

    </section>

</section>