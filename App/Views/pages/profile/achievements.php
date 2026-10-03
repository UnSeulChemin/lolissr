<?php

declare(strict_types=1);

use App\DTO\Common\Responses\ViewData;
use App\Services\Profile\ProfileImageCatalog;
use Framework\Support\Str;

/** @var ViewData $view */
/** @var string $section */
/** @var list<array{category: string, icon: string, title: string, current: int, target: int, unlocked: bool}> $achievements */
$unlockedCount = count(array_filter($achievements, static fn (array $item): bool => $item['unlocked']));
$category = '';
$filters = ['tout' => '✨'];
foreach ($achievements as $item)
{
    $filters[$item['category']] = $item['icon'];
}
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

    <div class="success-layout">
    <nav class="card profile-summary" aria-label="Filtrer les succès par catégorie">
        <h2>Sommaire</h2>
        <div class="profile-summary-links">
        <?php foreach ($filters as $key => $icon): ?>
            <?php $label = match ($key) { 'Vocabulaire' => 'Vocabulaires', 'Grammaire' => 'Grammaires', default => $key }; ?>
            <a class="profile-summary-link"
               href="<?= e($view->baseUri . 'profil/succes' . ($key === 'tout' ? '' : '/' . Str::asciiSlug($key))) ?>"
               aria-label="<?= e($key === 'tout' ? 'Tous les succès' : $label) ?>"
               title="<?= e($key === 'tout' ? 'Tous les succès' : $label) ?>"
               <?= $section === $key ? 'aria-current="page"' : '' ?>>
                <span aria-hidden="true"><?= e($icon) ?></span>
                <span><?= e($key === 'tout' ? 'Tout' : $label) ?></span>
            </a>
        <?php endforeach; ?>
        </div>
    </nav>

    <div class="success-content">
    <?php foreach ($achievements as $achievement): ?>
        <?php if ($section !== 'tout' && $section !== $achievement['category']) { continue; } ?>
        <?php if ($category !== $achievement['category']): ?>
            <?php if ($category !== ''): ?></div></section><?php endif; ?>
            <?php $category = $achievement['category']; ?>
            <section class="success-category" aria-label="<?= e(match ($category) { 'Vocabulaire' => 'Vocabulaires', 'Grammaire' => 'Grammaires', default => $category }) ?>">
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
                    <strong>Récompense : ⭐ <?= number_format(\App\Constants\AchievementRewards::FIGURINES[$achievement['target']], 0, ',', ' ') ?> XP</strong>
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
                <div class="success-reward success-reward-frame-xp">
                    <strong class="success-reward-xp-label">Récompense : ⭐ <?= number_format(\App\Constants\AchievementRewards::FIGURINES[$achievement['target']], 0, ',', ' ') ?> XP</strong>
                    <strong>Récompense : cadre<span class="success-reward-name">Ailes roses</span></strong>
                    <img src="<?= e($view->baseUri . 'images/profil/frame/thumbnail/' . ProfileImageCatalog::FIGURINE_REWARD_FRAME . '.webp') ?>" alt="Cadre rose à ailes" width="120" height="120">
                    <?php if ($achievement['unlocked']): ?>
                        <a href="<?= e($view->baseUri . 'profil/personnalisation') ?>">Choisir ce cadre</a>
                    <?php else: ?>
                        <span>🔒 Débloqué avec 8 figurines collectionnées</span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            <?php if ($achievement['category'] === 'Artbooks' && $achievement['target'] === \App\Constants\UserTitle::ARTBOOK_REWARD_TARGET): ?>
                <div class="success-reward success-reward-title">
                    <strong>Récompense : ⭐ <?= number_format(\App\Constants\AchievementRewards::ARTBOOKS[$achievement['target']], 0, ',', ' ') ?> XP</strong>
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
                    <strong class="success-reward-xp-label">Récompense : ⭐ <?= number_format(\App\Constants\AchievementRewards::ARTBOOKS[$achievement['target']], 0, ',', ' ') ?> XP</strong>
                    <strong>Récompense : cadre<span class="success-reward-name">Enluminure</span></strong>
                    <img src="<?= e($view->baseUri . 'images/profil/frame/thumbnail/' . ProfileImageCatalog::ARTBOOK_REWARD_FRAME . '.webp') ?>" alt="Cadre de livres illustrés, turquoise et doré" width="120" height="120">
                    <?php if ($achievement['unlocked']): ?>
                        <a href="<?= e($view->baseUri . 'profil/personnalisation') ?>">Choisir ce cadre</a>
                    <?php else: ?>
                        <span>🔒 Débloqué avec 25 artbooks lus</span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            <?php if ($achievement['category'] === 'Tomes' && $achievement['target'] === ProfileImageCatalog::TOME_REWARD_TARGET): ?>
                <div class="success-reward success-reward-banner">
                    <strong>Récompense : ⭐ <?= number_format(\App\Constants\AchievementRewards::TOMES[$achievement['target']], 0, ',', ' ') ?> XP</strong>
                    <strong>Récompense : bannière<span class="success-reward-name">Lecture sous les sakuras</span></strong>
                    <img src="<?= e($view->baseUri . 'images/profil/banner/thumbnail/' . ProfileImageCatalog::TOME_REWARD_BANNER . '.webp?v=20260929-sakura-v2') ?>" alt="Lectrice anime dans un jardin de cerisiers au crépuscule" width="600" height="200" loading="lazy">
                    <?php if ($achievement['unlocked']): ?>
                        <a href="<?= e($view->baseUri . 'profil/personnalisation') ?>">Choisir cette bannière</a>
                    <?php else: ?>
                        <span>🔒 Débloquée avec 100 tomes lus</span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            <?php if ($achievement['category'] === 'Tomes' && $achievement['target'] === ProfileImageCatalog::TOME_FRAME_TARGET): ?>
                <div class="success-reward success-reward-frame-xp">
                    <strong class="success-reward-xp-label">Récompense : ⭐ <?= number_format(\App\Constants\AchievementRewards::TOMES[$achievement['target']], 0, ',', ' ') ?> XP</strong>
                    <strong>Récompense : cadre<span class="success-reward-name">Grimoire céleste</span></strong>
                    <img src="<?= e($view->baseUri . 'images/profil/frame/thumbnail/' . ProfileImageCatalog::TOME_REWARD_FRAME . '.webp') ?>" alt="Cadre violet et argent décoré de livres et d’étoiles" width="120" height="120" loading="lazy">
                    <?php if ($achievement['unlocked']): ?>
                        <a href="<?= e($view->baseUri . 'profil/personnalisation') ?>">Choisir ce cadre</a>
                    <?php else: ?>
                        <span>🔒 Débloqué avec 200 tomes lus</span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            <?php if ($achievement['category'] === 'Tomes' && $achievement['target'] === \App\Constants\UserTitle::TOME_REWARD_TARGET): ?>
                <div class="success-reward success-reward-title">
                    <strong>Récompense : ⭐ <?= number_format(\App\Constants\AchievementRewards::TOMES[$achievement['target']], 0, ',', ' ') ?> XP</strong>
                    <strong>Récompense : titre</strong>
                    <span data-title-style="<?= $achievement['unlocked'] ? \App\Constants\UserTitle::styleForTitle(\App\Constants\UserTitle::TOME_REWARD) : '' ?>"><?= e(\App\Constants\UserTitle::TOME_REWARD) ?></span>
                    <?php if ($achievement['unlocked']): ?>
                        <a href="<?= e($view->baseUri . 'profil/personnalisation') ?>">Choisir ce titre</a>
                    <?php else: ?>
                        <span>🔒 Débloqué avec 50 tomes lus</span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            <?php if ($achievement['category'] === 'Tomes' && ! in_array($achievement['target'], [\App\Constants\UserTitle::TOME_REWARD_TARGET, ProfileImageCatalog::TOME_REWARD_TARGET, ProfileImageCatalog::TOME_FRAME_TARGET], true) && isset(\App\Constants\AchievementRewards::TOMES[$achievement['target']])): ?>
                <div class="success-reward success-reward-title success-reward-xp">
                    <strong>Récompense : ⭐ <?= \App\Constants\AchievementRewards::TOMES[$achievement['target']] ?> XP</strong>
                    <?php if (! $achievement['unlocked']): ?>
                        <span>🔒 Débloqué avec <?= $achievement['target'] ?> <?= $achievement['target'] === 1 ? 'tome lu' : 'tomes lus' ?></span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            <?php if ($achievement['category'] === 'Séries' && isset(\App\Constants\AchievementRewards::SERIES[$achievement['target']])): ?>
                <div class="success-reward success-reward-title <?= $achievement['target'] === \App\Constants\UserTitle::SERIES_REWARD_TARGET ? '' : 'success-reward-xp' ?>">
                    <strong>Récompense : ⭐ <?= number_format(\App\Constants\AchievementRewards::SERIES[$achievement['target']], 0, ',', ' ') ?> XP</strong>
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
                <div class="success-reward success-reward-title success-reward-xp success-reward-xp-top">
                    <strong>Récompense : ⭐ <?= number_format(\App\Constants\AchievementRewards::ARTBOOKS[1], 0, ',', ' ') ?> XP</strong>
                    <?php if (! $achievement['unlocked']): ?>
                        <span>🔒 Débloqué avec 1 artbook lu</span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            <?php if ($achievement['category'] === 'Figurines' && $achievement['target'] === 1): ?>
                <div class="success-reward success-reward-title success-reward-xp success-reward-xp-top">
                    <strong>Récompense : ⭐ <?= number_format(\App\Constants\AchievementRewards::FIGURINES[1], 0, ',', ' ') ?> XP</strong>
                    <?php if (! $achievement['unlocked']): ?>
                        <span>🔒 Débloqué avec 1 figurine collectionnée</span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            <?php if ($achievement['category'] === 'Nendoroids' && $achievement['target'] === ProfileImageCatalog::NENDOROID_FRAME_TARGET): ?>
                <div class="success-reward success-reward-frame-xp">
                    <strong class="success-reward-xp-label">Récompense : ⭐ <?= number_format(\App\Constants\AchievementRewards::NENDOROIDS[$achievement['target']], 0, ',', ' ') ?> XP</strong>
                    <strong>Récompense : cadre<span class="success-reward-name">Écrin des merveilles</span></strong>
                    <img src="<?= e($view->baseUri . 'images/profil/frame/thumbnail/' . ProfileImageCatalog::NENDOROID_REWARD_FRAME . '.webp') ?>" alt="Cadre lavande et or rose orné de joyaux étoilés" width="120" height="120" loading="lazy">
                    <?php if ($achievement['unlocked']): ?>
                        <a href="<?= e($view->baseUri . 'profil/personnalisation') ?>">Choisir ce cadre</a>
                    <?php else: ?>
                        <span>🔒 Débloqué avec 50 nendoroids collectionnés</span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            <?php if ($achievement['category'] === 'Nendoroids' && $achievement['target'] !== ProfileImageCatalog::NENDOROID_FRAME_TARGET && isset(\App\Constants\AchievementRewards::NENDOROIDS[$achievement['target']])): ?>
                <div class="success-reward success-reward-title <?= $achievement['target'] === ProfileImageCatalog::NENDOROID_REWARD_TARGET ? 'success-reward-banner' : ($achievement['target'] === \App\Constants\UserTitle::NENDOROID_REWARD_TARGET ? '' : 'success-reward-xp success-reward-xp-top') ?>">
                    <strong>Récompense : ⭐ <?= number_format(\App\Constants\AchievementRewards::NENDOROIDS[$achievement['target']], 0, ',', ' ') ?> XP</strong>
                    <?php if ($achievement['target'] === ProfileImageCatalog::NENDOROID_REWARD_TARGET): ?>
                        <strong>Récompense : bannière<span class="success-reward-name">Le petit monde des Nendoroids</span></strong>
                        <img src="<?= e($view->baseUri . 'images/profil/banner/thumbnail/' . ProfileImageCatalog::NENDOROID_REWARD_BANNER . '.webp') ?>" alt="Collection de figurines chibi dans une pièce fleurie au coucher du soleil" loading="lazy">
                        <?php if ($achievement['unlocked']): ?>
                            <a href="<?= e($view->baseUri . 'profil/personnalisation') ?>">Choisir cette bannière</a>
                        <?php endif; ?>
                    <?php elseif ($achievement['target'] === \App\Constants\UserTitle::NENDOROID_REWARD_TARGET): ?>
                        <strong>Récompense : titre</strong>
                        <span data-title-style="<?= $achievement['unlocked'] ? \App\Constants\UserTitle::styleForTitle(\App\Constants\UserTitle::NENDOROID_REWARD) : '' ?>"><?= e(\App\Constants\UserTitle::NENDOROID_REWARD) ?></span>
                        <?php if ($achievement['unlocked']): ?>
                            <a href="<?= e($view->baseUri . 'profil/personnalisation') ?>">Choisir ce titre</a>
                        <?php endif; ?>
                    <?php endif; ?>
                    <?php if (! $achievement['unlocked']): ?>
                        <span>🔒 Débloqué avec <?= $achievement['target'] ?> nendoroids collectionnés</span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            <?php if ($achievement['category'] === 'Peluches' && isset(\App\Constants\AchievementRewards::PELUCHES[$achievement['target']])): ?>
                <div class="success-reward <?= $achievement['target'] === ProfileImageCatalog::PELUCHE_FRAME_TARGET ? 'success-reward-frame-xp' : ($achievement['target'] === ProfileImageCatalog::PELUCHE_BANNER_TARGET ? 'success-reward-title success-reward-banner' : 'success-reward-title success-reward-xp success-reward-xp-top') ?>">
                    <strong class="success-reward-xp-label">Récompense : ⭐ <?= number_format(\App\Constants\AchievementRewards::PELUCHES[$achievement['target']], 0, ',', ' ') ?> XP</strong>
                    <?php if ($achievement['target'] === ProfileImageCatalog::PELUCHE_BANNER_TARGET): ?>
                        <strong>Récompense : bannière<span class="success-reward-name">Refuge des peluches</span></strong>
                        <img src="<?= e($view->baseUri . 'images/profil/banner/thumbnail/' . ProfileImageCatalog::PELUCHE_REWARD_BANNER . '.webp') ?>" alt="Peluches dans un refuge fleuri au coucher du soleil" loading="lazy">
                        <?php if ($achievement['unlocked']): ?>
                            <a href="<?= e($view->baseUri . 'profil/personnalisation') ?>">Choisir cette bannière</a>
                        <?php endif; ?>
                    <?php elseif ($achievement['target'] === ProfileImageCatalog::PELUCHE_FRAME_TARGET): ?>
                        <strong>Récompense : cadre<span class="success-reward-name">Cocon doré</span></strong>
                        <img src="<?= e($view->baseUri . 'images/profil/frame/thumbnail/' . ProfileImageCatalog::PELUCHE_REWARD_FRAME . '.webp') ?>" alt="Cadre doré avec un ours et un lapin en peluche" width="120" height="120" loading="lazy">
                        <?php if ($achievement['unlocked']): ?>
                            <a href="<?= e($view->baseUri . 'profil/personnalisation') ?>">Choisir ce cadre</a>
                        <?php endif; ?>
                    <?php endif; ?>
                    <?php if (! $achievement['unlocked']): ?>
                        <span>🔒 Débloqué avec <?= $achievement['target'] ?> <?= $achievement['target'] === 1 ? 'peluche collectionnée' : 'peluches collectionnées' ?></span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            <?php if ($achievement['category'] === 'Vocabulaire' && isset(\App\Constants\AchievementRewards::VOCABULARY[$achievement['target']])): ?>
                <div class="success-reward <?= $achievement['target'] === ProfileImageCatalog::LEARNING_FRAME_TARGET ? 'success-reward-frame-xp' : 'success-reward-title' ?> <?= $achievement['target'] === ProfileImageCatalog::LEARNING_BANNER_TARGET ? 'success-reward-banner' : ($achievement['target'] < \App\Constants\UserTitle::VOCABULARY_REWARD_TARGET ? 'success-reward-xp' : '') ?>">
                    <strong class="success-reward-xp-label">Récompense : ⭐ <?= number_format(\App\Constants\AchievementRewards::VOCABULARY[$achievement['target']], 0, ',', ' ') ?> XP</strong>
                    <?php if ($achievement['target'] === ProfileImageCatalog::LEARNING_BANNER_TARGET): ?>
                        <strong>Récompense : bannière<span class="success-reward-name">Bibliothèque des mots</span></strong>
                        <img src="<?= e($view->baseUri . 'images/profil/banner/thumbnail/' . ProfileImageCatalog::VOCABULARY_REWARD_BANNER . '.webp') ?>" alt="Bibliothèque des mots" loading="lazy">
                        <?php if ($achievement['unlocked']): ?>
                            <a href="<?= e($view->baseUri . 'profil/personnalisation') ?>">Choisir cette bannière</a>
                        <?php endif; ?>
                    <?php elseif ($achievement['target'] === ProfileImageCatalog::LEARNING_FRAME_TARGET): ?>
                        <strong>Récompense : cadre<span class="success-reward-name">Lexique de jade</span></strong>
                        <img src="<?= e($view->baseUri . 'images/profil/frame/thumbnail/' . ProfileImageCatalog::VOCABULARY_REWARD_FRAME . '.webp') ?>" alt="Cadre Lexique de jade" width="120" height="120" loading="lazy">
                        <?php if ($achievement['unlocked']): ?>
                            <a href="<?= e($view->baseUri . 'profil/personnalisation') ?>">Choisir ce cadre</a>
                        <?php endif; ?>
                    <?php endif; ?>
                    <?php if ($achievement['target'] === \App\Constants\UserTitle::VOCABULARY_REWARD_TARGET): ?>
                        <strong>Récompense : titre</strong>
                        <span data-title-style="<?= $achievement['unlocked'] ? \App\Constants\UserTitle::styleForTitle(\App\Constants\UserTitle::VOCABULARY_REWARD) : '' ?>"><?= e(\App\Constants\UserTitle::VOCABULARY_REWARD) ?></span>
                        <?php if ($achievement['unlocked']): ?>
                            <a href="<?= e($view->baseUri . 'profil/personnalisation') ?>">Choisir ce titre</a>
                        <?php endif; ?>
                    <?php endif; ?>
                    <?php if (! $achievement['unlocked']): ?>
                        <span>🔒 Débloqué avec <?= $achievement['target'] ?> <?= $achievement['target'] === 1 ? 'mot maîtrisé' : 'mots maîtrisés' ?></span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            <?php if ($achievement['category'] === 'Grammaire' && isset(\App\Constants\AchievementRewards::GRAMMAR[$achievement['target']])): ?>
                <div class="success-reward <?= $achievement['target'] === ProfileImageCatalog::LEARNING_FRAME_TARGET ? 'success-reward-frame-xp' : 'success-reward-title' ?> <?= $achievement['target'] === ProfileImageCatalog::LEARNING_BANNER_TARGET ? 'success-reward-banner' : ($achievement['target'] < \App\Constants\UserTitle::GRAMMAR_REWARD_TARGET ? 'success-reward-xp' : '') ?>">
                    <strong class="success-reward-xp-label">Récompense : ⭐ <?= number_format(\App\Constants\AchievementRewards::GRAMMAR[$achievement['target']], 0, ',', ' ') ?> XP</strong>
                    <?php if ($achievement['target'] === ProfileImageCatalog::LEARNING_BANNER_TARGET): ?>
                        <strong>Récompense : bannière<span class="success-reward-name">Atelier des phrases</span></strong>
                        <img src="<?= e($view->baseUri . 'images/profil/banner/thumbnail/' . ProfileImageCatalog::GRAMMAR_REWARD_BANNER . '.webp') ?>" alt="Atelier des phrases" loading="lazy">
                        <?php if ($achievement['unlocked']): ?>
                            <a href="<?= e($view->baseUri . 'profil/personnalisation') ?>">Choisir cette bannière</a>
                        <?php endif; ?>
                    <?php elseif ($achievement['target'] === ProfileImageCatalog::LEARNING_FRAME_TARGET): ?>
                        <strong>Récompense : cadre<span class="success-reward-name">Plume astrale</span></strong>
                        <img src="<?= e($view->baseUri . 'images/profil/frame/thumbnail/' . ProfileImageCatalog::GRAMMAR_REWARD_FRAME . '.webp') ?>" alt="Cadre Plume astrale" width="120" height="120" loading="lazy">
                        <?php if ($achievement['unlocked']): ?>
                            <a href="<?= e($view->baseUri . 'profil/personnalisation') ?>">Choisir ce cadre</a>
                        <?php endif; ?>
                    <?php endif; ?>
                    <?php if ($achievement['target'] === \App\Constants\UserTitle::GRAMMAR_REWARD_TARGET): ?>
                        <strong>Récompense : titre</strong>
                        <span data-title-style="<?= $achievement['unlocked'] ? \App\Constants\UserTitle::styleForTitle(\App\Constants\UserTitle::GRAMMAR_REWARD) : '' ?>"><?= e(\App\Constants\UserTitle::GRAMMAR_REWARD) ?></span>
                        <?php if ($achievement['unlocked']): ?>
                            <a href="<?= e($view->baseUri . 'profil/personnalisation') ?>">Choisir ce titre</a>
                        <?php endif; ?>
                    <?php endif; ?>
                    <?php if (! $achievement['unlocked']): ?>
                        <span>🔒 Débloqué avec <?= $achievement['target'] ?> <?= $achievement['target'] === 1 ? 'point de grammaire maîtrisé' : 'points de grammaire maîtrisés' ?></span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            <?php if ($achievement['category'] === 'Succès'): ?>
                <div class="success-reward">
                    <strong>Récompense : avatar<span class="success-reward-name"><?= e(ProfileImageCatalog::ACHIEVEMENT_AVATAR_NAMES[$achievement['target']]) ?></span></strong>
                    <img src="<?= e($view->baseUri . 'images/profil/avatar/thumbnail/' . ProfileImageCatalog::ACHIEVEMENT_REWARD_AVATARS[$achievement['target']] . '.webp') ?>" alt="<?= e(ProfileImageCatalog::ACHIEVEMENT_AVATAR_NAMES[$achievement['target']]) ?>" width="120" height="120" loading="lazy">
                    <?php if ($achievement['unlocked']): ?>
                        <a href="<?= e($view->baseUri . 'profil/personnalisation') ?>">Choisir cet avatar</a>
                    <?php else: ?>
                        <span>🔒 Débloqué avec <?= $achievement['target'] ?> <?= $achievement['target'] === 1 ? 'succès obtenu' : 'succès obtenus' ?></span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            <?php if ($achievement['category'] === 'Niveau'): ?>
                <?php $levelTitle = \App\Constants\UserTitle::LEVEL_REWARDS[$achievement['target']] ?? null; ?>
                <?php $levelFrame = ProfileImageCatalog::LEVEL_REWARD_FRAMES[$achievement['target']] ?? null; ?>
                <div class="success-reward success-reward-level <?= $levelFrame !== null ? '' : 'success-reward-title' ?> <?= $achievement['target'] === ProfileImageCatalog::LEVEL_BANNER_TARGET ? 'success-reward-banner' : '' ?>">
                    <?php if ($levelTitle !== null): ?>
                        <strong>Récompense : titre</strong>
                        <span data-title-style="<?= $achievement['unlocked'] ? \App\Constants\UserTitle::styleForTitle($levelTitle) : '' ?>"><?= e($levelTitle) ?></span>
                    <?php elseif ($levelFrame !== null): ?>
                        <strong>Récompense : cadre<span class="success-reward-name"><?= $achievement['target'] === 100 ? 'Ailes d’azur' : 'Ailes souveraines' ?></span></strong>
                        <img src="<?= e($view->baseUri . 'images/profil/frame/thumbnail/' . $levelFrame . '.webp') ?>" alt="Cadre ailé de niveau <?= $achievement['target'] ?>" width="120" height="120" loading="lazy">
                    <?php else: ?>
                        <strong>Récompense : bannière<span class="success-reward-name">Palais des étoiles</span></strong>
                        <img src="<?= e($view->baseUri . 'images/profil/banner/thumbnail/' . ProfileImageCatalog::LEVEL_REWARD_BANNER . '.webp') ?>" alt="Palais céleste au-dessus des nuages" loading="lazy">
                    <?php endif; ?>
                    <?php if ($achievement['unlocked']): ?>
                        <a href="<?= e($view->baseUri . 'profil/personnalisation') ?>"><?= $levelTitle !== null ? 'Choisir ce titre' : ($levelFrame !== null ? 'Choisir ce cadre' : 'Choisir cette bannière') ?></a>
                    <?php else: ?>
                        <span>🔒 Débloqué au niveau <?= $achievement['target'] ?></span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </article>
    <?php endforeach; ?>
    <?php if ($category !== ''): ?></div></section><?php endif; ?>
    </div>
    </div>
</section>
