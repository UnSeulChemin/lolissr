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
        <strong><?= $unlockedCount ?> / <?= count($achievements) ?></strong>
        <p>Succès débloqués</p>
        <progress value="<?= $unlockedCount ?>" max="<?= max(1, count($achievements)) ?>" aria-label="Succès débloqués"></progress>
        <p>Atteins tes objectifs et débloque des récompenses.</p>
        <p class="success-note">Selon tes statistiques actuelles.</p>
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
                    <strong>Récompense : cadre<span class="success-reward-name">Ailes roses</span></strong>
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
                    <strong>Récompense : ⭐ <?= number_format(\App\Services\Profile\AchievementXpService::ARTBOOK_REWARDS[$achievement['target']], 0, ',', ' ') ?> XP</strong>
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
                <div class="success-reward success-reward-frame-xp">
                    <strong class="success-reward-xp-label">Récompense : ⭐ <?= number_format(\App\Services\Profile\AchievementXpService::ARTBOOK_REWARDS[$achievement['target']], 0, ',', ' ') ?> XP</strong>
                    <strong>Récompense : cadre<span class="success-reward-name">Enluminure</span></strong>
                    <img src="<?= e($view->baseUri . 'images/profil/frame/thumbnail/' . ProfileImageCatalog::ARTBOOK_REWARD_FRAME . '.png') ?>" alt="Cadre de livres illustrés, turquoise et doré" width="120" height="120">
                    <?php if ($achievement['unlocked']): ?>
                        <a href="<?= e($view->baseUri . 'profil/personnalisation') ?>">Choisir ce cadre</a>
                    <?php else: ?>
                        <span>🔒 Débloqué avec 25 artbooks lus</span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            <?php if ($achievement['category'] === 'Tomes' && $achievement['target'] === ProfileImageCatalog::TOME_REWARD_TARGET): ?>
                <div class="success-reward success-reward-banner">
                    <strong>Récompense : ⭐ <?= number_format(\App\Services\Profile\AchievementXpService::TOME_REWARDS[$achievement['target']], 0, ',', ' ') ?> XP</strong>
                    <strong>Récompense : bannière<span class="success-reward-name">Lecture sous les sakuras</span></strong>
                    <img src="<?= e($view->baseUri . 'images/profil/banner/thumbnail/' . ProfileImageCatalog::TOME_REWARD_BANNER . '.png?v=20260929-sakura-v2') ?>" alt="Lectrice anime dans un jardin de cerisiers au crépuscule" width="600" height="200" loading="lazy">
                    <?php if ($achievement['unlocked']): ?>
                        <a href="<?= e($view->baseUri . 'profil/personnalisation') ?>">Choisir cette bannière</a>
                    <?php else: ?>
                        <span>🔒 Débloquée avec 100 tomes lus</span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            <?php if ($achievement['category'] === 'Tomes' && $achievement['target'] === ProfileImageCatalog::TOME_FRAME_TARGET): ?>
                <div class="success-reward success-reward-frame-xp">
                    <strong class="success-reward-xp-label">Récompense : ⭐ <?= number_format(\App\Services\Profile\AchievementXpService::TOME_REWARDS[$achievement['target']], 0, ',', ' ') ?> XP</strong>
                    <strong>Récompense : cadre<span class="success-reward-name">Grimoire céleste</span></strong>
                    <img src="<?= e($view->baseUri . 'images/profil/frame/thumbnail/' . ProfileImageCatalog::TOME_REWARD_FRAME . '.png') ?>" alt="Cadre violet et argent décoré de livres et d’étoiles" width="120" height="120" loading="lazy">
                    <?php if ($achievement['unlocked']): ?>
                        <a href="<?= e($view->baseUri . 'profil/personnalisation') ?>">Choisir ce cadre</a>
                    <?php else: ?>
                        <span>🔒 Débloqué avec 200 tomes lus</span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            <?php if ($achievement['category'] === 'Tomes' && $achievement['target'] === \App\Constants\UserTitle::TOME_REWARD_TARGET): ?>
                <div class="success-reward success-reward-title">
                    <strong>Récompense : ⭐ <?= number_format(\App\Services\Profile\AchievementXpService::TOME_REWARDS[$achievement['target']], 0, ',', ' ') ?> XP</strong>
                    <strong>Récompense : titre</strong>
                    <span data-title-style="<?= $achievement['unlocked'] ? \App\Constants\UserTitle::styleForTitle(\App\Constants\UserTitle::TOME_REWARD) : '' ?>"><?= e(\App\Constants\UserTitle::TOME_REWARD) ?></span>
                    <?php if ($achievement['unlocked']): ?>
                        <a href="<?= e($view->baseUri . 'profil/personnalisation') ?>">Choisir ce titre</a>
                    <?php else: ?>
                        <span>🔒 Débloqué avec 50 tomes lus</span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            <?php if ($achievement['category'] === 'Tomes' && ! in_array($achievement['target'], [\App\Constants\UserTitle::TOME_REWARD_TARGET, ProfileImageCatalog::TOME_REWARD_TARGET, ProfileImageCatalog::TOME_FRAME_TARGET], true) && isset(\App\Services\Profile\AchievementXpService::TOME_REWARDS[$achievement['target']])): ?>
                <div class="success-reward success-reward-title success-reward-xp">
                    <strong>Récompense : ⭐ <?= \App\Services\Profile\AchievementXpService::TOME_REWARDS[$achievement['target']] ?> XP</strong>
                    <?php if (! $achievement['unlocked']): ?>
                        <span>🔒 Débloqué avec <?= $achievement['target'] ?> <?= $achievement['target'] === 1 ? 'tome lu' : 'tomes lus' ?></span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            <?php if ($achievement['category'] === 'Séries' && isset(\App\Services\Profile\AchievementXpService::SERIES_REWARDS[$achievement['target']])): ?>
                <div class="success-reward success-reward-title <?= $achievement['target'] === \App\Constants\UserTitle::SERIES_REWARD_TARGET ? '' : 'success-reward-xp' ?>">
                    <strong>Récompense : ⭐ <?= number_format(\App\Services\Profile\AchievementXpService::SERIES_REWARDS[$achievement['target']], 0, ',', ' ') ?> XP</strong>
                    <?php if ($achievement['target'] === \App\Constants\UserTitle::SERIES_REWARD_TARGET): ?>
                        <strong>Récompense : titre</strong>
                        <span data-title-style="<?= $achievement['unlocked'] ? \App\Constants\UserTitle::styleForTitle(\App\Constants\UserTitle::SERIES_REWARD) : '' ?>"><?= e(\App\Constants\UserTitle::SERIES_REWARD) ?></span>
                        <?php if ($achievement['unlocked']): ?>
                            <a href="<?= e($view->baseUri . 'profil/personnalisation') ?>">Choisir ce titre</a>
                        <?php endif; ?>
                    <?php endif; ?>
                    <?php if (! $achievement['unlocked']): ?>
                        <span>🔒 Débloqué avec <?= $achievement['target'] ?> <?= $achievement['target'] === 1 ? 'série terminée' : 'séries terminées' ?></span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            <?php if ($achievement['category'] === 'Artbooks' && $achievement['target'] === 1): ?>
                <div class="success-reward success-reward-title success-reward-xp">
                    <strong>Récompense : ⭐ <?= number_format(\App\Services\Profile\AchievementXpService::ARTBOOK_REWARDS[1], 0, ',', ' ') ?> XP</strong>
                    <?php if (! $achievement['unlocked']): ?>
                        <span>🔒 Débloqué avec 1 artbook lu</span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </article>
    <?php endforeach; ?>
    <?php if ($category !== ''): ?></div></section><?php endif; ?>
</section>
