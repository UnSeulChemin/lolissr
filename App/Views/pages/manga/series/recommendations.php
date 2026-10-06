<?php
declare(strict_types=1);
/** @var \App\DTO\Common\Responses\ViewData $view */
/** @var list<array{id: string, title: string, reason: string, imageUrl: ?string, score: int, volumeCount: ?int, firstRelease: ?string, edition: ?string, categories: list<array{title: string, points: int}>}> $recommendations */
/** @var int $rankOffset */
/** @var int $currentPage */
/** @var int $totalPages */
/** @var string $paginationPath */
/** @var string $recommendationMode */
/** @var list<string> $favoriteIds */
?>
<section class="layout-container dashboard-page">
    <?php if ($recommendations === []): ?>
        <?php if (!in_array($recommendationMode, ['favorites', 'hidden'], true)): ?>
            <p class="collection-empty">Pas encore de suggestions disponibles pour ta collection.</p>
        <?php endif; ?>
    <?php else: ?>
        <section class="collection-grid u-grid u-justify-center" data-rank-offset="<?= $rankOffset ?>">
            <?php foreach ($recommendations as $rank => $recommendation): ?>
                <div class="collection-release-item u-stack">
                <div class="card collection-card collection-card-link collection-recommendation-card u-flex">
                    <span class="recommendation-rank" aria-label="<?= e('Rang ' . ($rankOffset + $rank + 1)) ?>"><?= $rankOffset + $rank + 1 ?></span>
                    <div class="recommendation-categories">
                        <?php foreach (array_slice($recommendation['categories'], 0, 3) as $category): ?>
                            <?php if ($recommendationMode === 'favorites'): ?>
                                <span class="recommendation-category"><?= $category['points'] ?> <?= e($category['title']) ?></span>
                            <?php elseif ($recommendationMode === 'authors'): ?>
                                <a class="recommendation-category" href="<?= e($view->baseUri . 'manga/series/recommandations-auteurs/auteur/' . \Framework\Support\Strings::asciiSlug($category['title'])) ?>"><?= $category['points'] ?> <?= e($category['title']) ?></a>
                            <?php else: ?>
                            <a class="recommendation-category" href="<?= e($view->baseUri . 'manga/series/recommandations/categorie/' . rawurlencode(mb_strtolower($category['title']))) ?>" title="<?= e($category['points'] . ' séries de ta collection : ' . $category['title']) ?>"><?= $category['points'] ?> <?= e($category['title']) ?></a>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                    <a class="recommendation-series-link" href="<?= e('https://www.mangacollec.com/series/' . $recommendation['id']) ?>" target="_blank" rel="noopener noreferrer" title="<?= e($recommendation['reason']) ?>">
                    <div class="card-image-box-portrait u-row-center u-clip">
                        <?php if ($recommendation['imageUrl'] !== null): ?>
                            <img class="card-image-portrait u-block u-w-full" src="<?= e(image_url($recommendation['imageUrl'])) ?>" alt="<?= e($recommendation['title']) ?>" loading="lazy" decoding="async">
                        <?php else: ?>
                            <span class="collection-upcoming-placeholder u-row-center u-w-full">Couverture indisponible</span>
                        <?php endif; ?>
                    </div>
                    <p class="collection-card-title u-block u-relative u-text-center u-clip u-bold"><?= e($recommendation['title']) ?></p>
                    <?php if ($recommendationMode !== 'hidden'): ?>
                    <p class="collection-card-subtitle u-relative u-text-center"><?= $recommendation['score'] ?> points<br>
                        <?php if ($recommendation['volumeCount'] !== null): ?>
                            <span title="<?= e('Édition : ' . ($recommendation['edition'] ?? 'Standard')) ?>"><?= $recommendation['volumeCount'] ?> tomes</span><br>
                        <?php endif; ?>
                        <?php if ($recommendation['firstRelease'] !== null): ?>
                            Début : <?= e($recommendation['firstRelease']) ?>
                        <?php endif; ?>
                    </p>
                    <?php endif; ?>
                    </a>
                </div>
                <?php $isFavorite = in_array($recommendation['id'], $favoriteIds, true); ?>
                <?php if ($recommendationMode === 'hidden'): ?>
                <form class="js-restore-recommendation" method="post" action="<?= e($view->baseUri . 'manga/series/recommandations/' . $recommendation['id'] . '/retablir') ?>">
                    <?= csrf_field() ?>
                    <button class="form-submit u-inline-center u-pointer u-semibold u-w-full" type="submit">Rétablir cette suggestion</button>
                </form>
                <?php else: ?>
                <form class="js-favorite-recommendation" data-favorites-page="<?= $recommendationMode === 'favorites' ? 'true' : 'false' ?>" method="post" action="<?= e($view->baseUri . 'manga/series/recommandations/' . $recommendation['id'] . '/favoris' . ($isFavorite ? '/retirer' : '')) ?>">
                    <?= csrf_field() ?>
                    <button class="form-submit u-inline-center u-pointer u-semibold u-w-full" type="submit" aria-pressed="<?= $isFavorite ? 'true' : 'false' ?>"><?= $isFavorite ? '♥ Retirer des favoris' : '♡ Ajouter aux favoris' ?></button>
                </form>
                <?php if ($recommendationMode !== 'favorites'): ?>
                <form class="js-hide-recommendation" method="post" action="<?= e($view->baseUri . 'manga/series/recommandations/' . $recommendation['id'] . '/masquer') ?>">
                    <?= csrf_field() ?>
                    <button class="form-submit u-inline-center u-pointer u-semibold u-w-full" type="submit">Masquer cette suggestion</button>
                </form>
                <?php endif; ?>
                <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </section>
    <?php endif; ?>
    <?php require view_path('pages/manga/series/partials/pagination.php'); ?>
</section>
