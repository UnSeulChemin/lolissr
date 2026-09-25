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
                ⛩️ Chinois
            </h1>

            <p class="dashboard-description u-relative">
                Apprends le chinois, révise du vocabulaire et explore le 晋语.
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
            href="<?= e($view->baseUri) ?>chinois/vocabulaire"
        >

            <span
                class="dashboard-card-icon u-row-center"
                aria-hidden="true"
            >
                📚
            </span>

            <span class="dashboard-card-title u-relative u-w-full u-bold">
                Vocabulaire
            </span>

            <span class="dashboard-card-description u-relative u-w-full">
                Mandarin, 晋语, expressions et vocabulaire chinois.
            </span>

        </a>

        <a
            class="
                card
                transition-card
                dashboard-card
             u-stack u-relative u-clip u-border-box"
            data-prefetch
            href="<?= e($view->baseUri) ?>chinois/grammaire"
        >

            <span
                class="dashboard-card-icon u-row-center"
                aria-hidden="true"
            >
                📖
            </span>

            <span class="dashboard-card-title u-relative u-w-full u-bold">
                Grammaire
            </span>

            <span class="dashboard-card-description u-relative u-w-full">
                Structures, règles et notes de grammaire chinoise.
            </span>

        </a>

        <a
            class="
                card
                transition-card
                dashboard-card
             u-stack u-relative u-clip u-border-box"
            data-prefetch
            href="<?= e($view->baseUri) ?>chinois/flashcards"
        >

            <span
                class="dashboard-card-icon u-row-center"
                aria-hidden="true"
            >
                🧠
            </span>

            <span class="dashboard-card-title u-relative u-w-full u-bold">
                Flashcards
            </span>

            <span class="dashboard-card-description u-relative u-w-full">
                Réviser automatiquement le vocabulaire enregistré.
            </span>

        </a>

        <a
            class="
                card
                transition-card
                dashboard-card
             u-stack u-relative u-clip u-border-box"
            data-prefetch
            href="<?= e($view->baseUri) ?>chinois/ajouter"
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
                Ajouter des mots, expressions et exemples en chinois.
            </span>

        </a>

    </section>

</section>