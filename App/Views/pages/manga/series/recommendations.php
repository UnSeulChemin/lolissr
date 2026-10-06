<?php
declare(strict_types=1);
/** @var \App\DTO\Common\Responses\ViewData $view */
/** @var list<array{id: string, title: string, reason: string, imageUrl: ?string, score: int, volumeCount: ?int, firstRelease: ?string, edition: ?string, categories: list<array{title: string, points: int}>}> $recommendations */
/** @var int $rankOffset */
/** @var int $currentPage */
/** @var int $totalPages */
/** @var string $paginationPath */
?>
<section class="layout-container dashboard-page">
    <?php if ($recommendations === []): ?>
        <p class="collection-empty">Pas encore de suggestions disponibles pour ta collection.</p>
    <?php else: ?>
        <section class="collection-grid u-grid u-justify-center" data-rank-offset="<?= $rankOffset ?>">
            <?php foreach ($recommendations as $rank => $recommendation): ?>
                <div class="collection-release-item u-stack">
                <a class="card collection-card collection-card-link collection-recommendation-card u-flex" href="<?= e('https://www.mangacollec.com/series/' . $recommendation['id']) ?>" target="_blank" rel="noopener noreferrer" title="<?= e($recommendation['reason']) ?>">
                    <span class="recommendation-rank" aria-label="<?= e('Rang ' . ($rankOffset + $rank + 1)) ?>"><?= $rankOffset + $rank + 1 ?></span>
                    <div class="recommendation-categories">
                        <?php foreach ($recommendation['categories'] as $category): ?>
                            <span class="recommendation-category" title="<?= e($category['points'] . ' séries de ta collection : ' . $category['title']) ?>"><?= $category['points'] ?> <?= e($category['title']) ?></span>
                        <?php endforeach; ?>
                    </div>
                    <div class="card-image-box-portrait u-row-center u-clip">
                        <?php if ($recommendation['imageUrl'] !== null): ?>
                            <img class="card-image-portrait u-block u-w-full" src="<?= e(image_url($recommendation['imageUrl'])) ?>" alt="<?= e($recommendation['title']) ?>" loading="lazy" decoding="async">
                        <?php else: ?>
                            <span class="collection-upcoming-placeholder u-row-center u-w-full">Couverture indisponible</span>
                        <?php endif; ?>
                    </div>
                    <p class="collection-card-title u-block u-relative u-text-center u-clip u-bold"><?= e($recommendation['title']) ?></p>
                    <p class="collection-card-subtitle u-relative u-text-center"><?= $recommendation['score'] ?> points<br>
                        <?php if ($recommendation['volumeCount'] !== null): ?>
                            <span title="<?= e('Édition : ' . ($recommendation['edition'] ?? 'Standard')) ?>"><?= $recommendation['volumeCount'] ?> tomes</span><br>
                        <?php endif; ?>
                        <?php if ($recommendation['firstRelease'] !== null): ?>
                            Début : <?= e($recommendation['firstRelease']) ?>
                        <?php endif; ?>
                    </p>
                </a>
                <form class="js-hide-recommendation" method="post" action="<?= e($view->baseUri . 'manga/series/recommandations/' . $recommendation['id'] . '/masquer') ?>">
                    <?= csrf_field() ?>
                    <button class="form-submit u-inline-center u-pointer u-semibold u-w-full" type="submit">Masquer cette suggestion</button>
                </form>
                </div>
            <?php endforeach; ?>
        </section>
    <?php endif; ?>
    <?php require view_path('pages/manga/series/partials/pagination.php'); ?>
</section>
