<?php

declare(strict_types=1);

$baseUri =
    rtrim(
        (string) ($baseUri ?? ''),
        '/',
    ) . '/';

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
                    Explorateur
                </p>

                <h1 class="profile-name">
                    LoliSSR
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
                    Niveau 1
                </div>

                <div class="profile-level-xp">
                    75 / 100 XP
                </div>

            </div>

            <div class="profile-level-progress">

                <div
                    class="profile-level-progress-bar"
                    style="width: 75%;"
                ></div>

            </div>

        </article>

    </section>

    <section class="profile-stats">

        <div class="profile-stat-row">

            <article class="card transition-card profile-stat-card">

                <h2 class="profile-stat-title">
                    📚 Tomes
                </h2>

                <p class="profile-stat-value">
                    190
                </p>

            </article>

            <article class="card transition-card profile-stat-card">

                <h2 class="profile-stat-title">
                    ⭐ Total XP
                </h2>

                <p class="profile-stat-value">
                    22 650 XP
                </p>

            </article>

        </div>

        <div class="profile-stat-row">

            <article class="card transition-card profile-stat-card">

                <h2 class="profile-stat-title">
                    📖 Séries
                </h2>

                <p class="profile-stat-value">
                    23
                </p>

            </article>

            <article class="card transition-card profile-stat-card">

                <h2 class="profile-stat-title">
                    ⭐ XP Séries
                </h2>

                <p class="profile-stat-value">
                    1 150 XP
                </p>

            </article>

        </div>

        <div class="profile-stat-row">

            <article class="card transition-card profile-stat-card">

                <h2 class="profile-stat-title">
                    🎓 Vocabulaires
                </h2>

                <p class="profile-stat-value">
                    1450
                </p>

            </article>

            <article class="card transition-card profile-stat-card">

                <h2 class="profile-stat-title">
                    ⭐ XP Vocabulaire
                </h2>

                <p class="profile-stat-value">
                    14 500 XP
                </p>

            </article>

        </div>

        <div class="profile-stat-row">

            <article class="card transition-card profile-stat-card">

                <h2 class="profile-stat-title">
                    📝 Grammaires
                </h2>

                <p class="profile-stat-value">
                    320
                </p>

            </article>

            <article class="card transition-card profile-stat-card">

                <h2 class="profile-stat-title">
                    ⭐ XP Grammaire
                </h2>

                <p class="profile-stat-value">
                    3 200 XP
                </p>

            </article>

        </div>

    </section>

</section>