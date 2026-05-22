<?php
declare(strict_types=1);

$currentPage = (int) ($currentPage ?? 1);
$totalPages  = (int) ($totalPages ?? 1);
$slugFilter  = $slugFilter ?? null;
$baseUri     = rtrim((string) ($baseUri ?? ''), '/') . '/';
$isSerieView = $isSerieView ?? false;
?>

<div class="collection-ajax-content">

    <?php include __DIR__ . '/series_ajax.php'; ?>

    <?php if (!$slugFilter && $totalPages > 1): ?>
    <nav class="collection-pagination-wrapper">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <a class="collection-pagination-link <?= $currentPage === $i ? 'active' : '' ?>"
           href="<?= $baseUri ?>manga/series/page/<?= $i ?>">
            <?= $i ?>
        </a>
        <?php endfor; ?>
    </nav>
    <?php endif; ?>

</div>