<?php

declare(strict_types=1);

/** @var App\Models\User $user */

$baseUri =
    rtrim(
        $baseUri
        ?? '',
        '/',
    ) . '/';

$avatarPath =
    "{$baseUri}images/avatar/thumbnail/{$user->avatar}.{$user->avatar_extension}";

$bannerPath =
    "{$baseUri}images/banner/thumbnail/{$user->banner}.{$user->banner_extension}";

$framePath =
    "{$baseUri}images/frame/thumbnail/{$user->frame}.{$user->frame_extension}";

$username =
    $user->username;

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

            <div class="profile-customization-banner">

                <img
                    src="<?= e($bannerPath) ?>"
                    alt="Bannière"
                    draggable="false"
                >

            </div>

            <div class="profile-customization-avatar">

                <img
                    class="profile-avatar-image"
                    src="<?= e($avatarPath) ?>"
                    alt="<?= e($username) ?>"
                    draggable="false"
                >

                <img
                    class="profile-frame"
                    src="<?= e($framePath) ?>"
                    alt=""
                    draggable="false"
                >

            </div>

            <h1 class="profile-customization-name">
                <?= e($username) ?>
            </h1>

            <p class="profile-customization-title">
                <?= e($user->title) ?>
            </p>

        </article>

        <section class="profile-customization-grid">

            <article
                class="
                    card
                    transition-card
                    profile-customization-card
                    js-profile-avatar
                "
            >

                <h2>
                    👤 Avatar
                </h2>

                <p>
                    Choisir un avatar.
                </p>

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

            <article
                class="
                    card
                    transition-card
                    profile-customization-card
                    js-profile-banner
                "
            >

                <h2>
                    🎨 Bannière
                </h2>

                <p>
                    Personnaliser le profil.
                </p>

            </article>

            <article
                class="
                    card
                    transition-card
                    profile-customization-card
                    js-profile-frame
                "
            >

                <h2>
                    ⭐ Cadre
                </h2>

                <p>
                    Choisir un cadre.
                </p>

            </article>

        </section>

    </section>

</section>
