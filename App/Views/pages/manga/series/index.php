<?php

declare(strict_types=1);

use App\DTO\Common\Responses\ViewData;
use App\DTO\Manga\Responses\MangaListItemData;

/** @var ViewData $view */
/** @var list<MangaListItemData> $mangas */
/** @var int $currentPage */
/** @var int $totalPages */
/** @var ?string $slugFilter */

$isSerieView =
    $slugFilter !== null;

?>

<section class="layout-container dashboard-page">

    <div class="collection-ajax-container">

        <?php require view_path('pages/manga/series/partials/items.php'); ?>

        <?php if ($totalPages > 1): ?>

            <nav class="collection-pagination-wrapper u-row-center">

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>

                    <?php

                    $class =
                        'collection-pagination-link'
                        . ($currentPage === $i ? ' active' : '');

                    ?>

                    <a draggable="false"
                        class="<?= $class ?>"
                        data-prefetch
                        <?= $currentPage === $i ? 'aria-current="page"' : '' ?>
                        href="<?= e($view->baseUri . 'manga/series/' . ($slugFilter !== null ? rawurlencode($slugFilter) . '/' : '')) ?>page/<?= $i ?>"
                    >
                        <?= $i ?>
                    </a>

                <?php endfor; ?>

            </nav>

        <?php endif; ?>

    </div>

</section>
