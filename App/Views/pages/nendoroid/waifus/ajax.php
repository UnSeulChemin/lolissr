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

    <section class="collection-grid u-grid u-justify-center">

        <?php foreach ($nendoroids as $nendoroid): ?>

            <?php

            $href =
                "{$view->baseUri}nendoroid/waifus/{$nendoroid->slug}/{$nendoroid->numero}";

            $thumbnail =
                "{$view->baseUri}images/nendoroid/thumbnail/{$nendoroid->thumbnail}.{$nendoroid->extension}";

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
                        src="<?= e($thumbnail) ?>"
                        alt="<?= e($nendoroid->waifu) ?>"
                        loading="lazy"
                        decoding="async"
                        draggable="false"
                    >

                </div>

                <p class="collection-card-title u-block u-relative u-text-center u-clip u-bold">
                    <?= e($nendoroid->waifu) ?>
                </p>

                <p class="collection-card-subtitle u-relative u-text-center">
                    <?= e($nendoroid->origin) ?>
                </p>

            </a>

        <?php endforeach; ?>

    </section>

</div>