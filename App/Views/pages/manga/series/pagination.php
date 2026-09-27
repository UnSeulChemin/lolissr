<?php

declare(strict_types=1);

use App\DTO\Common\Responses\ViewData;

/** @var ViewData $view */
/** @var int $currentPage */
/** @var int $totalPages */
/** @var string $paginationPath */

?>
<?php if ($totalPages > 1): ?>
    <nav class="collection-pagination-wrapper u-row-center" aria-label="Pagination">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a
                class="collection-pagination-link<?= $i === $currentPage ? ' active' : '' ?>"
                <?= $i === $currentPage ? 'aria-current="page"' : '' ?>
                data-prefetch
                href="<?= e($view->baseUri . $paginationPath) ?>/page/<?= $i ?>"
            ><?= $i ?></a>
        <?php endfor; ?>
    </nav>
<?php endif; ?>
