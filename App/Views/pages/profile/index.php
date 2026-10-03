<?php

declare(strict_types=1);

use App\DTO\Common\Responses\ViewData;
use App\Models\User;

/** @var ViewData $view */
/** @var User $user */
/** @var list<array{category: string, icon: string, title: string, current: int, target: int, unlocked: bool}> $achievements */
/** @var int $level */
/** @var int $currentXp */
/** @var int $xpRequired */
/** @var float $progress */

$avatarPath =
    "{$view->baseUri}images/profil/avatar/thumbnail/{$user->avatar}.{$user->avatar_extension}";

$bannerPath =
    "{$view->baseUri}images/profil/banner/thumbnail/{$user->banner}.{$user->banner_extension}?v=20260929-sakura-v2";

$framePath =
    "{$view->baseUri}images/profil/frame/thumbnail/{$user->frame}.{$user->frame_extension}";

?>

<section class="layout-container profile-page u-stack">

    <section class="profile-header-grid u-grid">

        <a
            href="<?= e($view->baseUri . 'profil/personnalisation') ?>"
            class="
                card
                transition-card
                profile-card
             u-stack u-w-full u-clip u-border-box"
        >

            <div class="profile-banner u-w-full u-clip">

                <img
                    src="<?= e($bannerPath) ?>"
                    alt="Bannière"
                    draggable="false"
                >

            </div>

            <div class="profile-avatar">

                <img
                    class="profile-avatar-image"
                    src="<?= e($avatarPath) ?>"
                    alt="<?= e($user->username) ?>"
                    draggable="false"
                >

                <img
                    class="profile-frame"
                    src="<?= e($framePath) ?>"
                    alt=""
                    draggable="false"
                >

            </div>

            <div class="profile-content u-text-center">

                <p class="profile-subtitle" data-title-style="<?= \App\Constants\UserTitle::styleForTitle($user->title) ?>">
                    <?= e($user->title) ?>
                </p>

                <h1 class="profile-name u-bold">
                    <?= e($user->username) ?>
                </h1>

            </div>

        </a>

        <a href="<?= e($view->baseUri . 'profil/succes') ?>" data-prefetch aria-label="Voir tous les succès"
            class="
                card
                transition-card
                profile-achievements
            "
        >

            <h2 class="profile-section-title u-text-center">
                🏆 Succès obtenus
            </h2>

            <?php $obtainedCount = count(array_filter($achievements, static fn (array $item): bool => $item['unlocked'])); ?>
            <div class="profile-achievements-summary">
                <p class="profile-achievements-count"><?= $obtainedCount ?> / <?= count($achievements) ?></p>
                <p>Succès débloqués</p>
                <progress value="<?= $obtainedCount ?>" max="<?= count($achievements) ?>" aria-label="Succès débloqués"></progress>
                <p class="profile-achievements-description">Atteins des objectifs de lecture, de collection et d’apprentissage pour débloquer des succès.</p>
            </div>
            <span class="profile-achievements-action">Voir mes succès et les prochains objectifs</span>
        </a>

    </section>

    <section class="profile-level-card-wrapper">

        <a href="<?= e($view->baseUri . 'profil/xp') ?>" data-prefetch aria-label="Voir le résumé de l’XP"
            class="
                card
                transition-card
                profile-level-card
             u-w-full u-clip u-border-box"
        >

            <div class="profile-level-header u-stack u-items-center">

                <div class="profile-level-label u-bold">
                    Niveau <?= $level ?>
                </div>

                <div class="profile-level-xp u-semibold">

                    <?= number_format($currentXp, 0, ',', ' ') ?>

                    /

                    <?= number_format($xpRequired, 0, ',', ' ') ?>

                    XP

                </div>

            </div>

            <div class="profile-level-progress u-relative u-w-full u-clip">

                <div
                    class="profile-level-progress-bar u-relative"
                    style="width: <?= $progress ?>%;"
                ></div>

            </div>

            <span class="profile-achievements-action profile-level-details">Voir le résumé de l’XP</span>
        </a>

    </section>

</section>
