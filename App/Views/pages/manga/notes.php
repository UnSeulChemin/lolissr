<?php

declare(strict_types=1);

$mangas =
    is_array($mangas ?? null)
        ? $mangas
        : [];

?>

<section class="layout-container dashboard-page">

    <div class="collection-ajax-container">

        <?php if ($mangas === []): ?>

            <article
                class="
                    card
                    transition-card
                "
            >

                <p class="home-empty">
                    🎉 Toutes les séries sont notées 10/10.
                </p>

            </article>

        <?php else: ?>

            <?php require view_path(
                'components/manga/series_ajax.php',
            ); ?>

        <?php endif; ?>

    </div>

</section>