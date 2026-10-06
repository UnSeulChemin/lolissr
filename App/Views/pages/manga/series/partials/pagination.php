<?php

declare(strict_types=1);

use App\DTO\Common\Responses\ViewData;

/** @var ViewData $view */
/** @var int $currentPage */
/** @var int $totalPages */
/** @var string $paginationPath */

?>
<?php $showHiddenLink = isset($recommendationMode) && (in_array($paginationPath, ['manga/series/recommandations', 'manga/series/recommandations-auteurs'], true) || $recommendationMode === 'hidden'); ?>
<?php if ($totalPages > 1 || $showHiddenLink): ?>
    <nav class="collection-pagination-wrapper u-row-center" aria-label="Pagination">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a draggable="false"
                class="collection-pagination-link<?= $i === $currentPage ? ' active' : '' ?>"
                <?= $i === $currentPage ? 'aria-current="page"' : '' ?>
                data-prefetch
                href="<?= e($view->baseUri . $paginationPath) ?>/page/<?= $i ?>"
            ><?= $i ?></a>
        <?php endfor; ?>
        <?php if ($showHiddenLink): ?>
            <a draggable="false" class="collection-pagination-link collection-pagination-icon" <?= $recommendationMode === 'hidden' ? 'data-history-back' : 'data-prefetch' ?> href="<?= e($view->baseUri . 'manga/series/' . ($recommendationMode === 'hidden' ? 'recommandations' : 'recommandations-masquees')) ?>" title="<?= $recommendationMode === 'hidden' ? 'Retour aux recommandations' : 'Suggestions masquées' ?>" aria-label="<?= $recommendationMode === 'hidden' ? 'Retour aux recommandations' : 'Suggestions masquées' ?>"><?= $recommendationMode === 'hidden' ? '↩' : '🙈' ?></a>
        <?php endif; ?>
    </nav>
<?php endif; ?>
