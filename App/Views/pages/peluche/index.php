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
                🧸 Peluches
            </h1>

            <p class="dashboard-description u-relative">
                Gère ta collection de peluches, ajoute de nouveaux modèles et consulte ta collection.
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
            href="<?= e($view->baseUri) ?>peluche/waifus"
        >

            <span
                class="dashboard-card-icon u-row-center"
                aria-hidden="true"
            >
                🧸
            </span>

            <span class="dashboard-card-title u-relative u-w-full u-bold">
                Waifus
            </span>

            <span class="dashboard-card-description u-relative u-w-full">
                Voir toutes les peluches enregistrées.
            </span>

        </a>

        <a
            class="
                card
                transition-card
                dashboard-card
             u-stack u-relative u-clip u-border-box"
            data-prefetch
            href="<?= e($view->baseUri) ?>peluche/ajouter"
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
                Ajouter une nouvelle peluche à la collection.
            </span>

        </a>

    </section>

</section>