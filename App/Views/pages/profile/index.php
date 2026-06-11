<?php

declare(strict_types=1);

/** @var App\Models\User $user */
/** @var int $level */
/** @var int $currentXp */
/** @var int $xpRequired */
/** @var float $progress */
/** @var int $readTomes */
/** @var int $totalXp */
/** @var int $completedSeries */
/** @var int $seriesXp */

$baseUri =
    rtrim(
        (string) ($baseUri ?? ''),
        '/',
    ) . '/';

$username =
    $user->username;

?>

<section class="layout-container profile-page">

    <section class="profile-header-grid">

        <article
            class="
                card
                transition-card
                profile-card
            "
        >

            <div class="profile-avatar">
                👤
            </div>

            <div class="profile-content">

                <p class="profile-subtitle">
                    Explorateur (Liste de titre? wtyle animé preminium)
                </p>

                <h1 class="profile-name">
                    <?= e($username) ?>
                </h1>

            </div>

        </article>

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

        <div class="profile-stat-row">

            <article class="card transition-card profile-stat-card">

                <h2 class="profile-stat-title">
                    📚 Tomes
                </h2>

                <p class="profile-stat-value">
                    <?= number_format($readTomes) ?>
                </p>

            </article>

            <article class="card transition-card profile-stat-card">

                <h2 class="profile-stat-title">
                    ⭐ Total XP
                </h2>

                <p class="profile-stat-value">

                    <?= number_format(
                        $totalXp,
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
                    📚 Séries terminées
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
                    🎓 Vocabulaires
                </h2>

                <p class="profile-stat-value">

                    <?= number_format(
                        $totalXp,
                        0,
                        ',',
                        ' ',
                    ) ?>

                    XP

                </p>

            </article>

            <article class="card transition-card profile-stat-card">

                <h2 class="profile-stat-title">
                    ⭐ XP Vocabulaire
                </h2>

                <p class="profile-stat-value">

                    <?= number_format(
                        $totalXp,
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
                    📝 Grammaires
                </h2>

                <p class="profile-stat-value">

                    <?= number_format(
                        $totalXp,
                        0,
                        ',',
                        ' ',
                    ) ?>

                    XP

                </p>

            </article>

            <article class="card transition-card profile-stat-card">

                <h2 class="profile-stat-title">
                    ⭐ XP Grammaire
                </h2>

                <p class="profile-stat-value">

                    <?= number_format(
                        $totalXp,
                        0,
                        ',',
                        ' ',
                    ) ?>

                    XP

                </p>

            </article>

        </div>

    </section>

</section>