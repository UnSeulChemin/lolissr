<?php

declare(strict_types=1);

use App\DTO\Common\Responses\ViewData;
use App\DTO\Peluche\Responses\PelucheListItemData;

/** @var ViewData $view */
/** @var list<PelucheListItemData> $peluches */
/** @var int $currentPage */
/** @var int $totalPages */

?>

<section class="layout-container dashboard-page">

    <div class="collection-ajax-container">

        <?php require view_path('pages/peluche/waifus/ajax.php'); ?>

        <?php if ($totalPages > 1): ?>

            <nav class="collection-pagination-wrapper">

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>

                    <?php

                    $paginationClass =
                        'collection-pagination-link'
                        . ($currentPage === $i ? ' active' : '');

                    ?>

                    <a
                        class="<?= e($paginationClass) ?>"
                        data-prefetch
                        href="<?= e("{$view->baseUri}peluche/waifus/page/{$i}") ?>"
                    >
                        <?= $i ?>
                    </a>

                <?php endfor; ?>

            </nav>

        <?php endif; ?>

    </div>

</section>