<?php

declare(strict_types=1);

use App\DTO\Common\Responses\ViewData;
use App\Services\Profile\ProfileImageCatalog;

/** @var ViewData $view */
/** @var list<array{category: string, icon: string, title: string, current: int, target: int, unlocked: bool}> $achievements */
$unlockedCount = count(array_filter($achievements, static fn (array $item): bool => $item['unlocked']));
$category = '';
?>

<section class="success-page">
    <header class="card success-heading">
        <h1>🏆 Mes succès</h1>
        <p>Retrouve les étapes de ta collection, de tes lectures et de ton apprentissage.</p>
        <strong><?= $unlockedCount ?> / <?= count($achievements) ?> succès obtenus</strong>
        <p class="success-note">La progression suit tes statistiques actuelles.</p>
    </header>

    <?php foreach ($achievements as $achievement): ?>
        <?php if ($category !== $achievement['category']): ?>
            <?php if ($category !== ''): ?></div></section><?php endif; ?>
            <?php $category = $achievement['category']; ?>
            <section class="success-category">
                <h2><?= e($achievement['icon'] . ' ' . $category) ?></h2>
                <div class="success-grid">
        <?php endif; ?>
        <article class="card success-item <?= $achievement['unlocked'] ? 'is-unlocked' : 'is-locked' ?>">
            <span class="success-icon" aria-hidden="true"><?= e($achievement['icon']) ?></span>
            <h3><?= e($achievement['title']) ?></h3>
            <p class="success-status"><?= $achievement['unlocked'] ? '✓ Obtenu' : '🔒 À débloquer' ?></p>
            <progress max="<?= $achievement['target'] ?>" value="<?= min($achievement['current'], $achievement['target']) ?>" aria-label="<?= e($achievement['title']) ?>"></progress>
            <span><?= min($achievement['current'], $achievement['target']) ?> / <?= $achievement['target'] ?></span>
            <?php if ($achievement['category'] === 'Figurines' && $achievement['target'] === \App\Constants\UserTitle::FIGURINE_REWARD_TARGET): ?>
                <div class="success-reward success-reward-title">
                    <strong>Récompense : titre</strong>
                    <span data-title-style="<?= $achievement['unlocked'] ? 'rose-blue' : '' ?>"><?= e(\App\Constants\UserTitle::FIGURINE_REWARD) ?></span>
                    <?php if ($achievement['unlocked']): ?>
                        <a href="<?= e($view->baseUri . 'profil/personnalisation') ?>">Choisir ce titre</a>
                    <?php else: ?>
                        <span>🔒 Débloqué avec 4 figurines collectionnées</span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            <?php if ($achievement['category'] === 'Figurines' && $achievement['target'] === ProfileImageCatalog::FIGURINE_REWARD_TARGET): ?>
                <div class="success-reward">
                    <strong>Récompense : cadre Ailes roses</strong>
                    <img src="<?= e($view->baseUri . 'images/profil/frame/thumbnail/' . ProfileImageCatalog::FIGURINE_REWARD_FRAME . '.png') ?>" alt="Cadre rose à ailes" width="120" height="120">
                    <?php if ($achievement['unlocked']): ?>
                        <a href="<?= e($view->baseUri . 'profil/personnalisation') ?>">Choisir ce cadre</a>
                    <?php else: ?>
                        <span>🔒 Débloqué avec 8 figurines collectionnées</span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            <?php if ($achievement['category'] === 'Artbooks' && $achievement['target'] === \App\Constants\UserTitle::ARTBOOK_REWARD_TARGET): ?>
                <div class="success-reward success-reward-title">
                    <strong>Récompense : titre</strong>
                    <span data-title-style="<?= $achievement['unlocked'] ? 'teal-gold' : '' ?>"><?= e(\App\Constants\UserTitle::ARTBOOK_REWARD) ?></span>
                    <?php if ($achievement['unlocked']): ?>
                        <a href="<?= e($view->baseUri . 'profil/personnalisation') ?>">Choisir ce titre</a>
                    <?php else: ?>
                        <span>🔒 Débloqué avec 10 artbooks lus</span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            <?php if ($achievement['category'] === 'Artbooks' && $achievement['target'] === ProfileImageCatalog::ARTBOOK_REWARD_TARGET): ?>
                <div class="success-reward">
                    <strong>Récompense : cadre Enluminure</strong>
                    <img src="<?= e($view->baseUri . 'images/profil/frame/thumbnail/' . ProfileImageCatalog::ARTBOOK_REWARD_FRAME . '.png') ?>" alt="Cadre de livres illustrés, turquoise et doré" width="120" height="120">
                    <?php if ($achievement['unlocked']): ?>
                        <a href="<?= e($view->baseUri . 'profil/personnalisation') ?>">Choisir ce cadre</a>
                    <?php else: ?>
                        <span>🔒 Débloqué avec 25 artbooks lus</span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </article>
    <?php endforeach; ?>
    <?php if ($category !== ''): ?></div></section><?php endif; ?>
</section>
