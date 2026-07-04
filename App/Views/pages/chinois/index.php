<?php

declare(strict_types=1);

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
                ⛩️ Chinois
            </h1>

            <p class="dashboard-description">
                Apprends le chinois, révise du vocabulaire et explore le 晋语.
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
            href="<?= e($view->baseUri) ?>chinois/vocabulaire"
        >

            <span
                class="dashboard-card-icon"
                aria-hidden="true"
            >
                📚
            </span>

            <span class="dashboard-card-title">
                Vocabulaire
            </span>

            <span class="dashboard-card-description">
                Mandarin, 晋语, expressions et vocabulaire chinois.
            </span>

        </a>

        <a
            class="
                card
                transition-card
                dashboard-card
            "
            data-prefetch
            href="<?= e($view->baseUri) ?>chinois/grammaire"
        >

            <span
                class="dashboard-card-icon"
                aria-hidden="true"
            >
                📖
            </span>

            <span class="dashboard-card-title">
                Grammaire
            </span>

            <span class="dashboard-card-description">
                Structures, règles et notes de grammaire chinoise.
            </span>

        </a>

        <a
            class="
                card
                transition-card
                dashboard-card
            "
            data-prefetch
            href="<?= e($view->baseUri) ?>chinois/flashcards"
        >

            <span
                class="dashboard-card-icon"
                aria-hidden="true"
            >
                🧠
            </span>

            <span class="dashboard-card-title">
                Flashcards
            </span>

            <span class="dashboard-card-description">
                Réviser automatiquement le vocabulaire enregistré.
            </span>

        </a>

        <a
            class="
                card
                transition-card
                dashboard-card
            "
            data-prefetch
            href="<?= e($view->baseUri) ?>chinois/ajouter"
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
                Ajouter des mots, expressions et exemples en chinois.
            </span>

        </a>

    </section>

</section>