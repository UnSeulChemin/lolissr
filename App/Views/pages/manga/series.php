<?php

declare(strict_types=1);

$mangas =
    $view['mangas'] ?? [];

$currentPage =
    (int) ($view['currentPage'] ?? 1);

$totalSeries =
    (int) ($view['totalSeries'] ?? 0);

$perPage =
    (int) ($view['perPage'] ?? 10);

$slugFilter =
    $view['slugFilter'] ?? null;

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

        <div
            class="collection-ajax-content"
        >

            <?php require view_path(
                'components/manga/series_ajax.php',
            ); ?>

            <?php if (
                ! $isSerieView
                && $totalPages > 1
            ): ?>

                <nav
                    class="
                        collection-pagination-wrapper
                    "
                >

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
                                    : ''
                                ?>
                            "
                            href="<?= e($baseUri) ?>manga/series/page/<?= $i ?>"
                        >
                            <?= $i ?>
                        </a>

                    <?php endfor; ?>

                </nav>

            <?php endif; ?>

        </div>

    </div>

</section>