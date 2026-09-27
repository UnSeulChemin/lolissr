<?php

declare(strict_types=1);

use App\DTO\Manga\Responses\MangaSeriesItemData;

/** @var list<MangaSeriesItemData> $mangas */

$paginationPath = 'manga/series/a-lire';

?>

<section class="layout-container dashboard-page">

    <div class="collection-ajax-container">

        <?php if ($mangas === []): ?>

            <article class="card transition-card">

                <p class="home-empty u-relative u-text-center">
                    🎉 Tous les tomes possédés sont lus.
                </p>

            </article>

        <?php else: ?>

            <?php

            $isSerieView = false;

            require view_path('pages/manga/series/ajax.php');

            ?>

        <?php endif; ?>

        <?php require view_path('pages/manga/series/pagination.php'); ?>

    </div>

</section>
