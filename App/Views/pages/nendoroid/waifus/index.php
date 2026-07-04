<?php

declare(strict_types=1);

$nendoroids =
    is_array($nendoroids ?? null)
        ? $nendoroids
        : [];

$currentPage =
    (int) ($currentPage ?? 1);

$totalWaifus =
    (int) ($totalWaifus ?? 0);

$perPage =
    (int) ($perPage ?? 10);

$totalPages =
    max(
        1,
        (int) ceil(
            $totalWaifus / $perPage,
        ),
    );

?>

<section class="layout-container dashboard-page">

    <div class="collection-ajax-container">

        <?php require view_path(
            'pages/nendoroid/waifus/ajax.php',
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
                        href="<?= e($view->baseUri) ?>nendoroid/waifus/page/<?= $i ?>"
                    >
                        <?= $i ?>
                    </a>

                <?php endfor; ?>

            </nav>

        <?php endif; ?>

    </div>

</section>