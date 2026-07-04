<?php

declare(strict_types=1);

use App\DTO\Common\Responses\ViewData;
use App\Models\User;

/** @var ViewData $view */
/** @var User $user */

$avatarPath =
    "{$view->baseUri}images/avatar/thumbnail/{$user->avatar}.{$user->avatar_extension}";

$bannerPath =
    "{$view->baseUri}images/banner/thumbnail/{$user->banner}.{$user->banner_extension}";

$framePath =
    "{$view->baseUri}images/frame/thumbnail/{$user->frame}.{$user->frame_extension}";

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

                <h2 class="home-card-title">
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

                <h2 class="home-card-title">
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

                <h2 class="home-card-title">
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

                <h2 class="home-card-title">
                    ⭐ Cadre
                </h2>

                <p>
                    Choisir un cadre.
                </p>

            </article>

        </section>

    </section>

</section>