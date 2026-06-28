<?php

declare(strict_types=1);

$artbooks =
    is_array($artbooks ?? null)
        ? $artbooks
        : [];

$currentPage =
    (int) ($currentPage ?? 1);

$totalArtbooks =
    (int) ($totalArtbooks ?? 0);

$perPage =
    (int) ($perPage ?? 10);

$baseUri =
    rtrim(
        (string) ($baseUri ?? ''),
        '/',
    ) . '/';

$totalPages =
    max(
        1,
        (int) ceil(
            $totalArtbooks / $perPage,
        ),
    );

?>

<section class="layout-container dashboard-page">

    <div class="collection-ajax-container">

        <?php require view_path(
            'pages/manga/artbooks/ajax.php',
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
                        href="<?= e($baseUri) ?>manga/artbooks/page/<?= $i ?>"
                    >
                        <?= $i ?>
                    </a>

                <?php endfor; ?>

            </nav>

        <?php endif; ?>

    </div>

</section>