<?php

declare(strict_types=1);

/** @var App\Models\User $user */

?>

<section class="layout-container">

    <section class="profile-customization">

        <article
            class="
                card
                transition-card
                profile-customization-hero
            "
        >

            <div class="profile-customization-avatar">
                👤
            </div>

            <h1 class="profile-customization-name">
                <?= e($user->username) ?>
            </h1>

            <p class="profile-customization-title">
                Explorateur
            </p>

        </article>

        <section class="profile-customization-grid">

            <article class="card transition-card">
                <h2>👤 Avatar</h2>
                <p>Choisir un avatar.</p>
            </article>

        <article
            class="
                card
                transition-card
                profile-customization-card
                js-profile-title
            "
        >

            <h2>
                🏆 Titre
            </h2>

            <p>
                Choisir un titre débloqué.
            </p>

        </article>

            <article class="card transition-card">
                <h2>🎨 Bannière</h2>
                <p>Personnaliser le profil.</p>
            </article>

            <article class="card transition-card">
                <h2>⭐ Cadre</h2>
                <p>Cadres spéciaux.</p>
            </article>

        </section>

    </section>

</section>