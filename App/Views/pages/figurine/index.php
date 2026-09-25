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
                🎀 Figurines
            </h1>

            <p class="dashboard-description u-relative">
                Gère ta collection de figurines, ajoute de nouvelles pièces et consulte tes vitrines.
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
            href="<?= e($view->baseUri) ?>figurine/waifus"
        >

            <span
                class="dashboard-card-icon u-row-center"
                aria-hidden="true"
            >
                🎀
            </span>

            <span class="dashboard-card-title u-relative u-w-full u-bold">
                Waifus
            </span>

            <span class="dashboard-card-description u-relative u-w-full">
                Voir toutes les figurines enregistrées.
            </span>

        </a>

        <a
            class="
                card
                transition-card
                dashboard-card
             u-stack u-relative u-clip u-border-box"
            data-prefetch
            href="<?= e($view->baseUri) ?>figurine/ajouter"
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
                Ajouter une nouvelle figurine à la collection.
            </span>

        </a>

        <a
            class="
                card
                transition-card
                dashboard-card
             u-stack u-relative u-clip u-border-box"
            data-prefetch
            href="<?= e($view->baseUri) ?>figurine/lien"
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
                Sites, boutiques et ressources utiles autour des figurines.
            </span>

        </a>

    </section>

</section>