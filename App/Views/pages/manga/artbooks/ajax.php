<?php

declare(strict_types=1);

use App\DTO\Common\Responses\ViewData;
use App\DTO\Manga\Responses\ArtbookSeriesItemData;

/** @var ViewData $view */
/** @var list<ArtbookSeriesItemData> $artbooks */

?>

<div class="collection-ajax-content">

    <?php if ($artbooks === []): ?>

        <p class="collection-empty">
            Aucun artbook trouvé.
        </p>

    </div>

    <?php return; endif; ?>

    <section class="collection-grid u-grid u-justify-center">

        <?php foreach ($artbooks as $artbook): ?>

            <a
                class="
                    card
                    transition-card
                    card-link
                    collection-card
                    collection-card-link
                 u-flex u-w-full"
                data-prefetch
                href="<?= e("{$view->baseUri}manga/artbooks/{$artbook->slug}/{$artbook->numero}") ?>"
            >

                <div class="card-image-box-portrait u-row-center u-clip">

                    <img
                        class="card-image-portrait u-block u-w-full"
                        src="<?= e($artbook->thumbnailUrl) ?>"
                        alt="<?= e($artbook->artbook) ?>"
                        loading="lazy"
                        decoding="async"
                        draggable="false"
                    >

                </div>

                <p class="collection-card-title u-block u-relative u-text-center u-clip u-bold">
                    <?= e($artbook->artbook) ?>
                </p>

                <p class="collection-card-subtitle u-relative u-text-center">
                    <?= e($artbook->subtitle) ?>
                </p>

            </a>

        <?php endforeach; ?>

    </section>

</div>