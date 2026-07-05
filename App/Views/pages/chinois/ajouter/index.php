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
            href="<?= e($view->baseUri) ?>chinois/ajouter/vocabulaire"
        >

            <span
                class="dashboard-card-icon"
                aria-hidden="true"
            >
                🈶
            </span>

            <span class="dashboard-card-title">
                Vocabulaire
            </span>

            <span class="dashboard-card-description">
                Ajouter un mot, une expression ou une phrase chinoise.
            </span>

        </a>

        <a
            class="
                card
                transition-card
                dashboard-card
            "
            data-prefetch
            href="<?= e($view->baseUri) ?>chinois/ajouter/grammaire"
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
                Ajouter une règle, une structure ou un exemple de grammaire.
            </span>

        </a>

    </section>

</section>