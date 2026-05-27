<?php

declare(strict_types=1);

$baseUri =
    rtrim(
        (string) ($baseUri ?? ''),
        '/',
    ) . '/';

?>

<section class="layout-container dashboard-page">

    <section class="dashboard-grid">

        <a
            class="
                card
                transition-card
                dashboard-card
            "
            data-prefetch
            href="<?= e($baseUri) ?>chinois/grammaire/hsk1"
        >

            <span
                class="dashboard-card-icon"
                aria-hidden="true"
            >
                一
            </span>

            <span class="dashboard-card-title">
                HSK1
            </span>

            <span class="dashboard-card-description">
                Débutant total.
            </span>

        </a>

        <a
            class="
                card
                transition-card
                dashboard-card
            "
            data-prefetch
            href="<?= e($baseUri) ?>chinois/grammaire/hsk2"
        >

            <span
                class="dashboard-card-icon"
                aria-hidden="true"
            >
                二
            </span>

            <span class="dashboard-card-title">
                HSK2
            </span>

            <span class="dashboard-card-description">
                Bases simples.
            </span>

        </a>

        <a
            class="
                card
                transition-card
                dashboard-card
            "
            data-prefetch
            href="<?= e($baseUri) ?>chinois/grammaire/hsk3"
        >

            <span
                class="dashboard-card-icon"
                aria-hidden="true"
            >
                三
            </span>

            <span class="dashboard-card-title">
                HSK3
            </span>

            <span class="dashboard-card-description">
                Intermédiaire débutant.
            </span>

        </a>

        <a
            class="
                card
                transition-card
                dashboard-card
            "
            data-prefetch
            href="<?= e($baseUri) ?>chinois/grammaire/hsk4"
        >

            <span
                class="dashboard-card-icon"
                aria-hidden="true"
            >
                四
            </span>

            <span class="dashboard-card-title">
                HSK4
            </span>

            <span class="dashboard-card-description">
                Intermédiaire solide.
            </span>

        </a>

    </section>

</section>