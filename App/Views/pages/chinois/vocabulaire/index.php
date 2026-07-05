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
            href="<?= e($view->baseUri) ?>chinois/vocabulaire/mandarin"
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
                Vocabulaire, expressions et chinois standard.
            </span>

        </a>

        <a
            class="
                card
                transition-card
                dashboard-card
            "
            data-prefetch
            href="<?= e($view->baseUri) ?>chinois/vocabulaire/jinyu"
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
                Dialecte 晋语, expressions locales et vocabulaire régional.
            </span>

        </a>

    </section>

</section>