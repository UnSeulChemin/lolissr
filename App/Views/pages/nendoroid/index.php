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
                🪆 Nendoroids
            </h1>

            <p class="dashboard-description">
                Gère ta collection de Nendoroids, ajoute de nouveaux modèles et consulte ta collection.
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
            href="<?= e($view->baseUri) ?>nendoroid/waifus"
        >

            <span
                class="dashboard-card-icon"
                aria-hidden="true"
            >
                🪆
            </span>

            <span class="dashboard-card-title">
                Waifus
            </span>

            <span class="dashboard-card-description">
                Voir tous les Nendoroids enregistrés.
            </span>

        </a>

        <a
            class="
                card
                transition-card
                dashboard-card
            "
            data-prefetch
            href="<?= e($view->baseUri) ?>nendoroid/ajouter"
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
                Ajouter un nouveau Nendoroid à la collection.
            </span>

        </a>

    </section>

</section>