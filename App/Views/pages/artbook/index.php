<?php

declare(strict_types=1);

use App\DTO\Artbook\Responses\ArtbookListItemData;
use App\DTO\Common\Responses\ViewData;

/** @var ViewData $view */
/**
 * @var list<ArtbookListItemData> $artbooks
 * @var int $currentPage
 * @var int $totalPages
 */

?>

<section class="layout-container dashboard-page">

    <div class="collection-ajax-container">

        <?php require view_path('pages/artbook/partials/items.php'); ?>

        <?php if ($totalPages > 1): ?>

            <nav class="collection-pagination-wrapper u-row-center">

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>

                    <?php

                    $class =
                        'collection-pagination-link'
                        . ($currentPage === $i ? ' active' : '');

                    ?>

                    <a
                        class="<?= $class ?>"
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