<?php
declare(strict_types=1);
/** @var \App\DTO\Common\Responses\ViewData $view */
/** @var list<\App\DTO\Manga\Responses\UpcomingMangaData> $releases */
?>
<section class="layout-container dashboard-page">
    <div class="collection-ajax-container">
        <div class="collection-ajax-content">
            <?php if ($releases === []): ?>
                <p class="collection-empty">Aucun tome dans cette liste pour le moment.</p>
            <?php else: ?>
                <section class="collection-grid u-grid u-justify-center">
                    <?php foreach ($releases as $release): ?>
                        <div class="collection-release-item u-stack">
                        <a class="card collection-card collection-card-link collection-card-upcoming u-flex u-w-full" data-prefetch href="<?= e($view->baseUri . 'manga/series/' . rawurlencode($release->slug)) ?>">
                            <span class="collection-status-badge collection-status-upcoming"><?= $release->isUpcoming ? 'À paraître' : 'Non possédé' ?></span>
                            <time class="collection-status-badge collection-release-date" datetime="<?= e($release->date) ?>"><?= e($release->dateLabel) ?></time>
                            <div class="card-image-box-portrait u-row-center u-clip">
                                <?php if ($release->imageUrl !== null): ?>
                                    <img class="card-image-portrait u-block u-w-full" src="<?= e(image_url($release->imageUrl)) ?>" alt="<?= e($release->title . ' — Tome ' . $release->number) ?>" loading="lazy" decoding="async">
                                <?php else: ?>
                                    <span class="collection-upcoming-placeholder u-row-center u-w-full">Tome <?= $release->number ?></span>
                                <?php endif; ?>
                            </div>
                            <p class="collection-card-title u-block u-relative u-text-center u-clip u-bold"><?= e($release->title) ?></p>
                            <p class="collection-card-subtitle u-relative u-text-center">Tome <?= $release->number ?></p>
                        </a>
                        <?php $releaseSlug = $release->slug; require view_path('pages/manga/series/partials/acquire-release.php'); ?>
                        </div>
                    <?php endforeach; ?>
                </section>
            <?php endif; ?>
        </div>
        <?php require view_path('pages/manga/series/partials/pagination.php'); ?>
    </div>
</section>
