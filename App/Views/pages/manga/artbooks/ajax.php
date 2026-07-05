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

    <section class="collection-grid">

        <?php foreach ($artbooks as $artbook): ?>

            <a
                class="
                    card
                    transition-card
                    card-link
                    collection-card
                    collection-card-link
                "
                data-prefetch
                href="<?= e($view->baseUri) ?>manga/artbooks/<?= e($artbook->slug) ?>/<?= $artbook->numero ?>"
            >

                <div class="card-image-box-portrait">

                    <img
                        class="card-image-portrait"
                        src="<?= e($artbook->thumbnailUrl) ?>"
                        alt="<?= e($artbook->artbook) ?>"
                        loading="lazy"
                        draggable="false"
                    >

                </div>

                <p class="collection-card-title">
                    <?= e($artbook->artbook) ?>
                </p>

                <p class="collection-card-subtitle">
                    <?= e($artbook->subtitle) ?>
                </p>

            </a>

        <?php endforeach; ?>

    </section>

</div>
