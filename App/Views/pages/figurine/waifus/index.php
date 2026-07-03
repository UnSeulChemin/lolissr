<?php

declare(strict_types=1);

use App\DTO\Figurine\Responses\FigurineSeriesItemData;

/** @var list<FigurineSeriesItemData> $figurines */

$baseUri = view_base_uri();

?>

<section class="layout-container dashboard-page">

    <div class="collection-ajax-container">

        <?php require view_path('pages/figurine/waifus/ajax.php'); ?>

        <?php if ($totalPages > 1): ?>

            <nav class="collection-pagination-wrapper">

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>

                    <a
                        class="collection-pagination-link <?= $currentPage === $i ? 'active' : '' ?>"
                        data-prefetch
                        href="<?= e($baseUri) ?>figurine/waifus/page/<?= $i ?>"
                    >
                        <?= $i ?>
                    </a>

                <?php endfor; ?>

            </nav>

        <?php endif; ?>

    </div>

</section>