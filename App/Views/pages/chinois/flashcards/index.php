<?php

declare(strict_types=1);

use App\DTO\Common\Responses\ViewData;

/** @var ViewData $view */

?>

<section class="layout-container dashboard-page">

    <section class="dashboard-grid u-grid u-justify-center">

        <a
            class="
                card
                transition-card
                dashboard-card
             u-stack u-relative u-clip u-border-box"
            data-prefetch
            href="<?= e($view->baseUri) ?>chinois/flashcards/vocabulaire"
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
                Mots, caractères et expressions.
            </span>

        </a>

        <a
            class="
                card
                transition-card
                dashboard-card
             u-stack u-relative u-clip u-border-box"
            data-prefetch
            href="<?= e($view->baseUri) ?>chinois/flashcards/grammaire"
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
                Structures et points de grammaire.
            </span>

        </a>

    </section>

</section>