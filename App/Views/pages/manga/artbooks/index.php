<?php

declare(strict_types=1);

use App\DTO\Common\Responses\ViewData;
use App\DTO\Manga\Responses\ArtbookSeriesItemData;

/** @var ViewData $view */
/**
 * @var list<ArtbookSeriesItemData> $artbooks
 * @var int $currentPage
 * @var int $totalPages
 */

?>

<section class="layout-container dashboard-page">

    <div class="collection-ajax-container">

        <?php require view_path('pages/manga/artbooks/ajax.php'); ?>

        <?php if ($totalPages > 1): ?>

            <nav class="collection-pagination-wrapper">

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>

                    <a
                        class="collection-pagination-link <?= $currentPage === $i ? 'active' : '' ?>"
                        data-prefetch
                        href="<?= e($view->baseUri) ?>manga/artbooks/page/<?= $i ?>"
                    >
                        <?= $i ?>
                    </a>

                <?php endfor; ?>

            </nav>

        <?php endif; ?>

    </div>

</section>