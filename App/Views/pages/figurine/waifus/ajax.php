<?php

declare(strict_types=1);

use App\DTO\Common\Responses\ViewData;
use App\DTO\Figurine\Responses\FigurineSeriesItemData;

/** @var ViewData $view */
/** @var list<FigurineSeriesItemData> $figurines */

?>

<div class="collection-ajax-content">

    <?php if ($figurines === []): ?>

        <p class="collection-empty">
            Aucune figurine trouvée.
        </p>

    </div>

    <?php return; endif; ?>

    <section class="collection-grid">

        <?php foreach ($figurines as $figurine): ?>

            <a
                class="
                    card
                    transition-card
                    card-link
                    collection-card
                    collection-card-link
                "
                data-prefetch
                href="<?= e($view->baseUri) ?>figurine/waifus/<?= e($figurine->slug) ?>/<?= $figurine->numero ?>"
            >

                <div class="card-image-box-portrait">

                    <img
                        class="card-image-portrait"
                        src="<?= e($figurine->thumbnailUrl) ?>"
                        alt="<?= e($figurine->waifu) ?>"
                        loading="lazy"
                        draggable="false"
                    >

                </div>

                <p class="collection-card-title">
                    <?= e($figurine->waifu) ?>
                </p>

                <p class="collection-card-subtitle">
                    <?= e($figurine->origin) ?>
                </p>

            </a>

        <?php endforeach; ?>

    </section>

</div>