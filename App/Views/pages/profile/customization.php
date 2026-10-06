<?php

declare(strict_types=1);

use App\DTO\Common\Responses\ViewData;
use App\Models\User\User;

/** @var ViewData $view */
/** @var User $user */

$avatarPath =
    "{$view->baseUri}images/profil/avatar/thumbnail/{$user->avatar}.{$user->avatar_extension}";

$bannerPath =
    "{$view->baseUri}images/profil/banner/thumbnail/{$user->banner}.{$user->banner_extension}";

$framePath =
    "{$view->baseUri}images/profil/frame/thumbnail/{$user->frame}.{$user->frame_extension}";

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
             u-stack u-relative u-clip"
        >

            <div class="profile-customization-banner u-w-full u-clip">

                <img
                    src="<?= e(image_url($bannerPath)) ?>"
                    alt="Bannière"
                    draggable="false"
                >

            </div>

            <div class="profile-customization-avatar">

                <img
                    class="profile-avatar-image"
                    src="<?= e(image_url($avatarPath)) ?>"
                    alt="<?= e($username) ?>"
                    draggable="false"
                >

                <img
                    class="profile-frame"
                    src="<?= e(image_url($framePath)) ?>"
                    alt=""
                    draggable="false"
                >

            </div>

            <h1 class="profile-customization-name u-text-center u-bold">
                <?= e($username) ?>
            </h1>

            <p class="profile-customization-title" data-title-style="<?= \App\Constants\Profile\UserTitle::styleForTitle($user->title) ?>">
                <?= e($user->title) ?>
            </p>

        </article>

        <section class="profile-customization-grid u-grid">

            <article
                class="
                    card
                    transition-card
                    profile-customization-card
                    js-profile-frame
                 u-pointer"
            >

                <h2 class="home-card-title">
                    <span class="customization-icon" aria-hidden="true">🖼️</span>
                    Cadre
                </h2>

                <p>
                    Choisir un cadre.
                </p>

            </article>

            <article
                class="
                    card
                    transition-card
                    profile-customization-card
                    js-profile-avatar
                 u-pointer"
            >

                <h2 class="home-card-title">
                    <span class="customization-icon" aria-hidden="true">👤</span>
                    Avatar
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
                 u-pointer"
            >

                <h2 class="home-card-title">
                    <span class="customization-icon" aria-hidden="true">🏷️</span>
                    Titre
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
                 u-pointer"
            >

                <h2 class="home-card-title">
                    <span class="customization-icon" aria-hidden="true">🌄</span>
                    Bannière
                </h2>

                <p>
                    Choisir une bannière.
                </p>

            </article>



        </section>

    </section>

</section>
