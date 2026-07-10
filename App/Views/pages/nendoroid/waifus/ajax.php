<?php

declare(strict_types=1);

use App\DTO\Common\Responses\ViewData;
use App\DTO\Nendoroid\Responses\NendoroidData;

/** @var ViewData $view */
/** @var list<NendoroidData> $nendoroids */

?>

<div class="collection-ajax-content">

    <?php if ($nendoroids === []): ?>

        <p class="collection-empty">
            Aucun Nendoroid trouvé.
        </p>

    </div>

    <?php return; endif; ?>

    <section class="collection-grid">

        <?php foreach ($nendoroids as $nendoroid): ?>

            <a
                class="
                    card
                    transition-card
                    card-link
                    collection-card
                    collection-card-link
                "
                data-prefetch
                href="<?= e($view->baseUri) ?>nendoroid/waifus/<?= e($nendoroid->slug) ?>/<?= $nendoroid->numero ?>"
            >

                <div class="card-image-box-portrait">

                    <img
                        class="card-image-portrait"
                        src="<?= e($view->baseUri) ?>images/nendoroid/thumbnail/<?= e($nendoroid->thumbnail) ?>.<?= e($nendoroid->extension) ?>"
                        alt="<?= e($nendoroid->waifu) ?>"
                        loading="lazy"
                        draggable="false"
                    >

                </div>

                <p class="collection-card-title">
                    <?= e($nendoroid->waifu) ?>
                </p>

                <p class="collection-card-subtitle">
                    <?= e($nendoroid->origin) ?>
                </p>

            </a>

        <?php endforeach; ?>

    </section>

</div>