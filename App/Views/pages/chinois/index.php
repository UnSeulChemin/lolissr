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
            href="<?= e($baseUri) ?>chinois/mandarin"
        >

            <span
                class="dashboard-card-icon"
                aria-hidden="true"
            >
                中文
            </span>

            <span class="dashboard-card-title">
                Mandarin
            </span>

            <span class="dashboard-card-description">
                Mots, expressions et vocabulaire en chinois standard.
            </span>

        </a>

        <a
            class="
                card
                transition-card
                dashboard-card
            "
            data-prefetch
            href="<?= e($baseUri) ?>chinois/jinyu"
        >

            <span
                class="dashboard-card-icon"
                aria-hidden="true"
            >
                晋语
            </span>

            <span class="dashboard-card-title">
                JinYu
            </span>

            <span class="dashboard-card-description">
                Mots, expressions locales et tournures du 晋语.
            </span>

        </a>

        <a
            class="
                card
                transition-card
                dashboard-card
            "
            data-prefetch
            href="<?= e($baseUri) ?>chinois/grammaire"
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
            href="<?= e($baseUri) ?>chinois/ajouter"
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

        <a
            class="
                card
                transition-card
                dashboard-card
            "
            data-prefetch
            href="<?= e($baseUri) ?>chinois/flashcards"
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

    </section>

</section>