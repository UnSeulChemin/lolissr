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
            href="<?= e($view->baseUri) ?>chinois/vocabulaire/mandarin"
        >

            <span
                class="dashboard-card-icon u-row-center"
                aria-hidden="true"
            >
                中文
            </span>

            <span class="dashboard-card-title u-relative u-w-full u-bold">
                Mandarin
            </span>

            <span class="dashboard-card-description u-relative u-w-full">
                Vocabulaire, expressions et chinois standard.
            </span>

        </a>

        <a
            class="
                card
                transition-card
                dashboard-card
             u-stack u-relative u-clip u-border-box"
            data-prefetch
            href="<?= e($view->baseUri) ?>chinois/vocabulaire/jinyu"
        >

            <span
                class="dashboard-card-icon u-row-center"
                aria-hidden="true"
            >
                晋语
            </span>

            <span class="dashboard-card-title u-relative u-w-full u-bold">
                JinYu
            </span>

            <span class="dashboard-card-description u-relative u-w-full">
                Dialecte 晋语, expressions locales et vocabulaire régional.
            </span>

        </a>

    </section>

</section>