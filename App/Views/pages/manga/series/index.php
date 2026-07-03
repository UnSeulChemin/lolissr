<?php

declare(strict_types=1);

use App\DTO\Manga\Responses\MangaSeriesItemData;

/** @var list<MangaSeriesItemData> $mangas */
/** @var int $currentPage */
/** @var int $totalPages */
/** @var ?string $slugFilter */

$isSerieView = $slugFilter !== null;

$baseUri = view_base_uri();

?>

<section class="layout-container dashboard-page">

    <div class="collection-ajax-container">

        <?php require view_path(
            'pages/manga/series/ajax.php',
        ); ?>

        <?php if (
            !$isSerieView
            && $totalPages > 1
        ): ?>

            <nav class="collection-pagination-wrapper">

                <?php for (
                    $i = 1;
                    $i <= $totalPages;
                    $i++
                ): ?>

                    <a
                        class="
                            collection-pagination-link
                            <?= $currentPage === $i
                                ? 'active'
                                : '' ?>
                        "
                        data-prefetch
                        href="<?= e($baseUri) ?>manga/series/page/<?= $i ?>"
                    >
                        <?= $i ?>
                    </a>

                <?php endfor; ?>

            </nav>

        <?php endif; ?>

    </div>

</section>