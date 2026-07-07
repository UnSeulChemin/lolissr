<?php

declare(strict_types=1);

use App\DTO\Common\Responses\ViewData;
use App\DTO\Chinois\Responses\ChinoisVocabulaireData;

/** @var ViewData $view */
/** @var string $langue */
/** @var list<ChinoisVocabulaireData> $vocabulaires */
/** @var int $currentPage */
/** @var int $totalPages */

?>

<section class="layout-container dashboard-page">

    <div class="collection-ajax-container">

        <?php require view_path(
            'pages/chinois/vocabulaire/ajax.php',
        ); ?>

        <?php if ($totalPages > 1): ?>

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
                        href="<?= e($view->baseUri) ?>chinois/vocabulaire/<?= e($langue) ?>/page/<?= $i ?>"
                    >
                        <?= $i ?>
                    </a>

                <?php endfor; ?>

            </nav>

        <?php endif; ?>

    </div>

</section>