<?php

declare(strict_types=1);

use App\DTO\Chinois\Responses\ChinoisVocabulaireData;
use App\DTO\Common\Responses\ViewData;

/** @var ViewData $view */
/** @var string $langue */
/** @var list<ChinoisVocabulaireData> $vocabulaires */
/** @var int $currentPage */
/** @var int $totalPages */

?>

<section class="layout-container dashboard-page">

    <div class="collection-ajax-container">

        <?php require view_path('pages/chinois/vocabulaire/partials/items.php'); ?>

        <?php if ($totalPages > 1): ?>

            <nav class="collection-pagination-wrapper u-row-center">

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>

                    <a draggable="false"
                        class="
                            collection-pagination-link
                            <?= $currentPage === $i
                                ? 'active'
                                : '' ?>
                        "
                        data-prefetch
                        href="<?= e($view->baseUri) ?>chinois/vocabulaire/<?= e($langue) ?>/page/<?= $i ?>"
                    >
                        <?= $i ?>
                    </a>

                <?php endfor; ?>

            </nav>

        <?php endif; ?>

    </div>

</section>