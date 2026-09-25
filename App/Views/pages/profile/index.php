<?php

declare(strict_types=1);

use App\DTO\Common\Responses\ViewData;
use App\Models\User;

/** @var ViewData $view */
/** @var User $user */
/** @var int $level */
/** @var int $currentXp */
/** @var int $xpRequired */
/** @var float $progress */
/** @var int $readTomes */
/** @var int $tomeXp */
/** @var int $completedSeries */
/** @var int $seriesXp */
/** @var int $readArtbooks */
/** @var int $artbookXp */
/** @var int $figurinesCollected */
/** @var int $figurinesXp */
/** @var int $nendoroidsCollected */
/** @var int $nendoroidsXp */
/** @var int $peluchesCollected */
/** @var int $peluchesXp */
/** @var int $vocabularyLearned */
/** @var int $vocabularyXp */
/** @var int $grammarLearned */
/** @var int $grammarXp */
/** @var int $totalProfileXp */

$avatarPath =
    "{$view->baseUri}images/avatar/thumbnail/{$user->avatar}.{$user->avatar_extension}";

$bannerPath =
    "{$view->baseUri}images/banner/thumbnail/{$user->banner}.{$user->banner_extension}";

$framePath =
    "{$view->baseUri}images/frame/thumbnail/{$user->frame}.{$user->frame_extension}";

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

                <p class="profile-subtitle">
                    <?= e($user->title) ?>
                </p>

                <h1 class="profile-name u-bold">
                    <?= e($user->username) ?>
                </h1>

            </div>

        </a>

        <article
            class="
                card
                transition-card
                profile-achievements
            "
        >

            <h2 class="profile-section-title u-text-center">
                🏆 Succès récents
            </h2>

            <div class="achievement-list u-row-center">

                <div class="achievement-item">
                    📚 Premier tome ajouté
                </div>

                <div class="achievement-item">
                    🎓 1000 mots appris
                </div>

                <div class="achievement-item">
                    📖 20 séries collectionnées
                </div>

                <div class="achievement-item">
                    |...]
                </div>

            </div>

        </article>

    </section>

    <section class="profile-level-card-wrapper">

        <article
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

                    <?= number_format(
                        $currentXp,
                        0,
                        ',',
                        ' ',
                    ) ?>

                    /

                    <?= number_format(
                        $xpRequired,
                        0,
                        ',',
                        ' ',
                    ) ?>

                    XP

                </div>

            </div>

            <div class="profile-level-progress u-relative u-w-full u-clip">

                <div
                    class="profile-level-progress-bar u-relative"
                    style="width: <?= $progress ?>%;"
                ></div>

            </div>

        </article>

    </section>

    <h2 class="home-section-title u-relative u-text-center u-w-full">
        📊 Résumé de l'XP gagnée
    </h2>

    <section class="profile-stats u-stack">

        <div class="profile-stat-row u-grid">

            <article class="card transition-card profile-stat-card u-justify-center u-w-full u-border-box">

                <h2 class="profile-stat-title u-bold">
                    📈 Progression totale
                </h2>

                <p class="profile-stat-value u-bold">
                    Niveau <?= $level ?>
                </p>

            </article>

            <article class="card transition-card profile-stat-card u-justify-center u-w-full u-border-box">

                <h2 class="profile-stat-title u-bold">
                    ⭐ XP Totale
                </h2>

                <p class="profile-stat-value u-bold">
                    <?= number_format($totalProfileXp, 0, ',', ' ') ?>
                    XP
                </p>

            </article>

        </div>

        <div class="profile-stat-row u-grid">

            <article class="card transition-card profile-stat-card u-justify-center u-w-full u-border-box">

                <h2 class="profile-stat-title u-bold">
                    📚 Tomes lus
                </h2>

                <p class="profile-stat-value u-bold">
                    <?= number_format($readTomes) ?>
                </p>

            </article>

            <article class="card transition-card profile-stat-card u-justify-center u-w-full u-border-box">

                <h2 class="profile-stat-title u-bold">
                    📚 XP Tomes
                </h2>

                <p class="profile-stat-value u-bold">
                    <?= number_format($tomeXp, 0, ',', ' ') ?>
                    XP
                </p>

            </article>

        </div>

        <div class="profile-stat-row u-grid">

            <article class="card transition-card profile-stat-card u-justify-center u-w-full u-border-box">

                <h2 class="profile-stat-title u-bold">
                    📖 Séries terminées
                </h2>

                <p class="profile-stat-value u-bold">
                    <?= number_format($completedSeries) ?>
                </p>

            </article>

            <article class="card transition-card profile-stat-card u-justify-center u-w-full u-border-box">

                <h2 class="profile-stat-title u-bold">
                    🏆 XP Séries
                </h2>

                <p class="profile-stat-value u-bold">
                    <?= number_format($seriesXp, 0, ',', ' ') ?>
                    XP
                </p>

            </article>

        </div>

        <div class="profile-stat-row u-grid">

            <article class="card transition-card profile-stat-card u-justify-center u-w-full u-border-box">

                <h2 class="profile-stat-title u-bold">
                    📕 Artbooks lus
                </h2>

                <p class="profile-stat-value u-bold">
                    <?= number_format($readArtbooks) ?>
                </p>

            </article>

            <article class="card transition-card profile-stat-card u-justify-center u-w-full u-border-box">

                <h2 class="profile-stat-title u-bold">
                    ⭐ XP Artbooks
                </h2>

                <p class="profile-stat-value u-bold">
                    <?= number_format($artbookXp, 0, ',', ' ') ?>
                    XP
                </p>

            </article>

        </div>

        <div class="profile-stat-row u-grid">

            <article class="card transition-card profile-stat-card u-justify-center u-w-full u-border-box">

                <h2 class="profile-stat-title u-bold">
                    🎀 Figurines
                </h2>

                <p class="profile-stat-value u-bold">
                    <?= number_format($figurinesCollected) ?>
                </p>

            </article>

            <article class="card transition-card profile-stat-card u-justify-center u-w-full u-border-box">

                <h2 class="profile-stat-title u-bold">
                    ⭐ XP Figurines
                </h2>

                <p class="profile-stat-value u-bold">
                    <?= number_format($figurinesXp, 0, ',', ' ') ?>
                    XP
                </p>

            </article>

        </div>

        <div class="profile-stat-row u-grid">

            <article class="card transition-card profile-stat-card u-justify-center u-w-full u-border-box">

                <h2 class="profile-stat-title u-bold">
                    🪆 Nendoroids
                </h2>

                <p class="profile-stat-value u-bold">
                    <?= number_format($nendoroidsCollected) ?>
                </p>

            </article>

            <article class="card transition-card profile-stat-card u-justify-center u-w-full u-border-box">

                <h2 class="profile-stat-title u-bold">
                    ⭐ XP Nendoroids
                </h2>

                <p class="profile-stat-value u-bold">
                    <?= number_format($nendoroidsXp, 0, ',', ' ') ?>
                    XP
                </p>

            </article>

        </div>

        <div class="profile-stat-row u-grid">

            <article class="card transition-card profile-stat-card u-justify-center u-w-full u-border-box">

                <h2 class="profile-stat-title u-bold">
                    🧸 Peluches
                </h2>

                <p class="profile-stat-value u-bold">
                    <?= number_format($peluchesCollected) ?>
                </p>

            </article>

            <article class="card transition-card profile-stat-card u-justify-center u-w-full u-border-box">

                <h2 class="profile-stat-title u-bold">
                    ⭐ XP Peluches
                </h2>

                <p class="profile-stat-value u-bold">
                    <?= number_format($peluchesXp, 0, ',', ' ') ?>
                    XP
                </p>

            </article>

        </div>

        <div class="profile-stat-row u-grid">

            <article class="card transition-card profile-stat-card u-justify-center u-w-full u-border-box">

                <h2 class="profile-stat-title u-bold">
                    🎓 Vocabulaire appris
                </h2>

                <p class="profile-stat-value u-bold">
                    <?= number_format($vocabularyLearned) ?>
                </p>

            </article>

            <article class="card transition-card profile-stat-card u-justify-center u-w-full u-border-box">

                <h2 class="profile-stat-title u-bold">
                    ⭐ XP Vocabulaire
                </h2>

                <p class="profile-stat-value u-bold">
                    <?= number_format($vocabularyXp, 0, ',', ' ') ?>
                    XP
                </p>

            </article>

        </div>

        <div class="profile-stat-row u-grid">

            <article class="card transition-card profile-stat-card u-justify-center u-w-full u-border-box">

                <h2 class="profile-stat-title u-bold">
                    📝 Grammaires
                </h2>

                <p class="profile-stat-value u-bold">
                    <?= number_format($grammarLearned) ?>
                </p>

            </article>

            <article class="card transition-card profile-stat-card u-justify-center u-w-full u-border-box">

                <h2 class="profile-stat-title u-bold">
                    ⭐ XP Grammaire
                </h2>

                <p class="profile-stat-value u-bold">
                    <?= number_format($grammarXp, 0, ',', ' ') ?>
                    XP
                </p>

            </article>

        </div>

    </section>

</section>