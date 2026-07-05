<?php

declare(strict_types=1);

use App\DTO\Common\Responses\ViewData;

/** @var ViewData $view */

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
            href="<?= e($view->baseUri) ?>chinois/flashcards/vocabulaire"
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
                Mots, caractères et expressions.
            </span>

        </a>

        <a
            class="
                card
                transition-card
                dashboard-card
            "
            data-prefetch
            href="<?= e($view->baseUri) ?>chinois/flashcards/grammaire"
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
                Structures et points de grammaire.
            </span>

        </a>

    </section>

</section>