<?php

declare(strict_types=1);

use App\DTO\Common\Responses\ViewData;
use App\DTO\Peluche\Responses\PelucheListItemData;

/** @var ViewData $view */
/** @var list<PelucheListItemData> $peluches */

?>

<div class="collection-ajax-content">

    <?php if ($peluches === []): ?>

        <p class="collection-empty">
            Aucune peluche trouvée.
        </p>

    </div>

    <?php return; endif; ?>

    <section class="collection-grid">

        <?php foreach ($peluches as $peluche): ?>

            <?php

            $href =
                "{$view->baseUri}peluche/waifus/{$peluche->slug}/{$peluche->numero}";

            ?>

            <a
                class="
                    card
                    transition-card
                    card-link
                    collection-card
                    collection-card-link
                "
                data-prefetch
                href="<?= e($href) ?>"
            >

                <div class="card-image-box-portrait">

                    <?php if ($peluche->thumbnailUrl !== null): ?>

                        <img
                            class="card-image-portrait"
                            src="<?= e($peluche->thumbnailUrl) ?>"
                            alt="<?= e($peluche->waifu) ?>"
                            loading="lazy"
                            decoding="async"
                            draggable="false"
                        >

                    <?php endif; ?>

                </div>

                <p class="collection-card-title">
                    <?= e($peluche->waifu) ?>
                </p>

                <p class="collection-card-subtitle">
                    <?= e($peluche->origin) ?>
                </p>

            </a>

        <?php endforeach; ?>

    </section>

</div>