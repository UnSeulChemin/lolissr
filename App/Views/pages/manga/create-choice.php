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
            href="<?= e($view->baseUri) ?>manga/ajouter/manga"
        >

            <span
                class="dashboard-card-icon u-row-center"
                aria-hidden="true"
            >
                📚
            </span>

            <span class="dashboard-card-title u-relative u-w-full u-bold">
                Manga
            </span>

            <span class="dashboard-card-description u-relative u-w-full">
                Ajouter un manga à la collection avec sa jaquette, sa note et ses informations.
            </span>

        </a>

        <a
            class="
                card
                transition-card
                dashboard-card
             u-stack u-relative u-clip u-border-box"
            data-prefetch
            href="<?= e($view->baseUri) ?>manga/ajouter/artbook"
        >

            <span
                class="dashboard-card-icon u-row-center"
                aria-hidden="true"
            >
                📕
            </span>

            <span class="dashboard-card-title u-relative u-w-full u-bold">
                Artbook
            </span>

            <span class="dashboard-card-description u-relative u-w-full">
                Ajouter un artbook à la collection avec sa couverture, sa note et ses informations.
            </span>

        </a>

    </section>

</section>