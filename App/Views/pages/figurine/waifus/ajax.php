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

    <section class="collection-grid u-grid u-justify-center">

        <?php foreach ($figurines as $figurine): ?>

            <?php

            $href =
                "{$view->baseUri}figurine/waifus/{$figurine->slug}/{$figurine->numero}";

            ?>

            <a
                class="
                    card
                    transition-card
                    card-link
                    collection-card
                    collection-card-link
                 u-flex u-w-full"
                data-prefetch
                href="<?= e($href) ?>"
            >

                <div class="card-image-box-portrait u-row-center u-clip">

                    <img
                        class="card-image-portrait u-block u-w-full"
                        src="<?= e($figurine->thumbnailUrl) ?>"
                        alt="<?= e($figurine->waifu) ?>"
                        loading="lazy"
                        decoding="async"
                        draggable="false"
                    >

                </div>

                <p class="collection-card-title u-block u-relative u-text-center u-clip u-bold">
                    <?= e($figurine->waifu) ?>
                </p>

                <p class="collection-card-subtitle u-relative u-text-center">
                    <?= e($figurine->origin) ?>
                </p>

            </a>

        <?php endforeach; ?>

    </section>

</div>