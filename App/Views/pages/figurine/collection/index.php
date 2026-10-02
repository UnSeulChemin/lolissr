<?php

declare(strict_types=1);

use App\DTO\Common\Responses\ViewData;
use App\DTO\Figurine\Responses\FigurineListItemData;

/** @var ViewData $view */
/** @var list<FigurineListItemData> $figurines */
/** @var int $currentPage */
/** @var int $totalPages */

?>

<section class="layout-container dashboard-page">

    <div class="collection-ajax-container">

        <?php require view_path('pages/figurine/collection/partials/items.php'); ?>

        <?php if ($totalPages > 1): ?>

            <nav class="collection-pagination-wrapper u-row-center">

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>

                    <?php

                    $paginationClass =
                        'collection-pagination-link'
                        . ($currentPage === $i ? ' active' : '');

                    ?>

                    <a
                        class="<?= e($paginationClass) ?>"
                        data-prefetch
                        href="<?= e("{$view->baseUri}figurine/waifus/page/{$i}") ?>"
                    >
                        <?= $i ?>
                    </a>

                <?php endfor; ?>

            </nav>

        <?php endif; ?>

    </div>

</section>