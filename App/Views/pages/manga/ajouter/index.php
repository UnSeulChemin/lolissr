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
            href="<?= e($view->baseUri) ?>manga/ajouter/manga"
        >

            <span
                class="dashboard-card-icon"
                aria-hidden="true"
            >
                📚
            </span>

            <span class="dashboard-card-title">
                Manga
            </span>

            <span class="dashboard-card-description">
                Ajouter un manga à la collection avec sa jaquette, sa note et ses informations.
            </span>

        </a>

        <a
            class="
                card
                transition-card
                dashboard-card
            "
            data-prefetch
            href="<?= e($view->baseUri) ?>manga/ajouter/artbook"
        >

            <span
                class="dashboard-card-icon"
                aria-hidden="true"
            >
                📕
            </span>

            <span class="dashboard-card-title">
                Artbook
            </span>

            <span class="dashboard-card-description">
                Ajouter un artbook à la collection avec sa couverture, sa note et ses informations.
            </span>

        </a>

    </section>

</section>