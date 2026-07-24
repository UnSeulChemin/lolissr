<?php

declare(strict_types=1);

use App\DTO\Manga\Responses\MangaSeriesItemData;

/** @var list<MangaSeriesItemData> $mangas */

?>

<section class="layout-container dashboard-page">

    <div class="collection-ajax-container">

        <?php if ($mangas === []): ?>

            <article class="card transition-card">

                <p class="home-empty">
                    🎉 Toutes les séries sont terminées.
                </p>

            </article>

        <?php else: ?>

            <?php

            $isSerieView = false;

            require view_path('pages/manga/series/ajax.php');

            ?>

        <?php endif; ?>

    </div>

</section>