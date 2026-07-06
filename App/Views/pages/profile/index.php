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
/** @var int $totalProfileXp */
/** @var int $vocabularyLearned */
/** @var int $vocabularyXp */
/** @var int $grammarLearned */
/** @var int $grammarXp */
/** @var int $figurinesCollected */
/** @var int $figurinesXp */

$avatarPath =
    "{$view->baseUri}images/avatar/thumbnail/{$user->avatar}.{$user->avatar_extension}";

$bannerPath =
    "{$view->baseUri}images/banner/thumbnail/{$user->banner}.{$user->banner_extension}";

$framePath =
    "{$view->baseUri}images/frame/thumbnail/{$user->frame}.{$user->frame_extension}";

?>

<section class="layout-container profile-page">

    <section class="profile-header-grid">

        <a
            href="<?= e($view->baseUri . 'profil/personnalisation') ?>"
            class="
                card
                transition-card
                profile-card
            "
        >

            <div class="profile-banner">

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

            <div class="profile-content">

                <p class="profile-subtitle">
                    <?= e($user->title) ?>
                </p>

                <h1 class="profile-name">
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

            <h2 class="profile-section-title">
                🏆 Succès récents
            </h2>

            <div class="achievement-list">

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
            "
        >

            <div class="profile-level-header">

                <div class="profile-level-label">
                    Niveau <?= $level ?>
                </div>

                <div class="profile-level-xp">

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

            <div class="profile-level-progress">

                <div
                    class="profile-level-progress-bar"
                    style="width: <?= $progress ?>%;"
                ></div>

            </div>

        </article>

    </section>

    <h2 class="home-section-title">
        📊 Résumé de l'XP gagnée
    </h2>

    <section class="profile-stats">

        <section class="profile-stat-row">

            <article class="card transition-card profile-stat-card">

                <h2 class="profile-stat-title">
                    📈 Progression totale
                </h2>

                <p class="profile-stat-value">
                    Niveau <?= $level ?>
                </p>

            </article>

            <article class="card transition-card profile-stat-card">

                <h2 class="profile-stat-title">
                    ⭐ XP Totale
                </h2>

                <p class="profile-stat-value">

                    <?= number_format(
                        $totalProfileXp,
                        0,
                        ',',
                        ' ',
                    ) ?>

                    XP

                </p>

            </article>

        </section>

        <div class="profile-stat-row">

            <article class="card transition-card profile-stat-card">

                <h2 class="profile-stat-title">
                    📚 Tomes lus
                </h2>

                <p class="profile-stat-value">
                    <?= number_format($readTomes) ?>
                </p>

            </article>

            <article class="card transition-card profile-stat-card">

                <h2 class="profile-stat-title">
                    📚 XP Tomes
                </h2>

                <p class="profile-stat-value">

                    <?= number_format(
                        $tomeXp,
                        0,
                        ',',
                        ' ',
                    ) ?>

                    XP

                </p>

            </article>

        </div>

        <div class="profile-stat-row">

            <article class="card transition-card profile-stat-card">

                <h2 class="profile-stat-title">
                    📖 Séries terminées
                </h2>

                <p class="profile-stat-value">
                    <?= number_format($completedSeries) ?>
                </p>

            </article>

            <article class="card transition-card profile-stat-card">

                <h2 class="profile-stat-title">
                    🏆 XP Séries
                </h2>

                <p class="profile-stat-value">

                    <?= number_format(
                        $seriesXp,
                        0,
                        ',',
                        ' ',
                    ) ?>

                    XP

                </p>

            </article>

        </div>

        <div class="profile-stat-row">

            <article class="card transition-card profile-stat-card">

                <h2 class="profile-stat-title">
                    🧸 Figurines
                </h2>

                <p class="profile-stat-value">
                    <?= number_format($figurinesCollected) ?>
                </p>

            </article>

            <article class="card transition-card profile-stat-card">

                <h2 class="profile-stat-title">
                    ⭐ XP Figurines
                </h2>

                <p class="profile-stat-value">

                    <?= number_format(
                        $figurinesXp,
                        0,
                        ',',
                        ' ',
                    ) ?>

                    XP

                </p>

            </article>

        </div>

        <div class="profile-stat-row">

            <article class="card transition-card profile-stat-card">

                <h2 class="profile-stat-title">
                    🎓 Vocabulaire appris
                </h2>

                <p class="profile-stat-value">
                    <?= number_format($vocabularyLearned) ?>
                </p>

            </article>

            <article class="card transition-card profile-stat-card">

                <h2 class="profile-stat-title">
                    ⭐ XP Vocabulaire
                </h2>

                <p class="profile-stat-value">
                    <?= number_format($vocabularyXp, 0, ',', ' ') ?>
                    XP
                </p>

            </article>

        </div>

        <div class="profile-stat-row">

            <article class="card transition-card profile-stat-card">

                <h2 class="profile-stat-title">
                    📝 Grammaires
                </h2>

                <p class="profile-stat-value">
                    <?= number_format($grammarLearned) ?>
                </p>

            </article>

            <article class="card transition-card profile-stat-card">

                <h2 class="profile-stat-title">
                    ⭐ XP Grammaire
                </h2>

                <p class="profile-stat-value">
                    <?= number_format($grammarXp, 0, ',', ' ') ?>
                    XP
                </p>

            </article>

        </div>

    </section>

</section>