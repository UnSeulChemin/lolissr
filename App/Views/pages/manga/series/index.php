<?php

declare(strict_types=1);

$mangas =
    is_array($mangas ?? null)
        ? $mangas
        : [];

$currentPage =
    (int) ($currentPage ?? 1);

$totalSeries =
    (int) ($totalSeries ?? 0);

$perPage =
    (int) ($perPage ?? 10);

$slugFilter =
    $slugFilter ?? null;

$isSerieView =
    is_string($slugFilter)
    && trim($slugFilter) !== '';

$baseUri =
    rtrim(
        (string) ($baseUri ?? ''),
        '/',
    ) . '/';

$totalPages =
    max(
        1,
        (int) ceil(
            $totalSeries / $perPage,
        ),
    );

?>

<section class="layout-container dashboard-page">

    <div class="collection-ajax-container">

        <?php require view_path(
            'pages/manga/series/ajax.php',
        ); ?>

        <?php if (
            !$isSerieView
            && $totalPages > 1
        ): ?>

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
                        href="<?= e($baseUri) ?>manga/series/page/<?= $i ?>"
                    >
                        <?= $i ?>
                    </a>

                <?php endfor; ?>

            </nav>

        <?php endif; ?>

    </div>

</section>