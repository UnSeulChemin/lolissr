<?php

declare(strict_types=1);

$baseUri = rtrim(
    (string) ($baseUri ?? ''),
    '/',
) . '/';

$currentSearch =
    isset($currentSearch)
        ? (string) $currentSearch
        : '';

?>

<header>

    <nav>

        <?php if (is_logged()): ?>

            <?php

            /** @var App\Models\User|null $user */
            $user = user();

            assert($user !== null);

            $username =
                $user->username;

            $usernameMain =
                strlen($username) > 3
                    ? substr($username, 0, -3)
                    : $username;

            $usernameSuffix =
                strlen($username) > 3
                    ? substr($username, -3)
                    : '';

            $level =
                $user->level;

            ?>

            <div class="site-profile">

                <a
                    class="site-profile-link"
                    href="<?= e($baseUri) ?>profil"
                    title="<?= e($username) ?>"
                >

                    <span class="site-logo">

                        <span class="site-logo-loli">
                            <?= e($usernameMain) ?>
                        </span>

                        <?php if ($usernameSuffix !== ''): ?>

                            <span class="site-logo-ssr">
                                <?= e($usernameSuffix) ?>
                            </span>

                        <?php endif; ?>

                    </span>

                    <span class="site-logo-level">
                        <?= e((string) $level) ?>
                    </span>

                </a>

            </div>

            <ul>

                <li>

                    <a
                        class="nav-link-icon"
                        data-prefetch
                        href="<?= e($baseUri) ?>"
                        title="Accueil"
                    >
                        🏠
                    </a>

                </li>

                <li>

                    <a
                        class="nav-link-icon"
                        data-prefetch
                        href="<?= e($baseUri) ?>manga"
                        title="Manga"
                    >
                        📚
                    </a>

                </li>

                <li>

                    <a
                        class="nav-link-icon"
                        data-prefetch
                        href="<?= e($baseUri) ?>chinois"
                        title="Chinois"
                    >
                        ⛩️
                    </a>

                </li>

                <li>

                    <a
                        class="nav-link-icon"
                        data-confirm-logout
                        href="<?= e($baseUri) ?>deconnexion"
                        title="Déconnexion"
                    >
                        🚪
                    </a>

                </li>

            </ul>

            <div class="header-search-area">

                <form
                    class="header-search js-header-search"
                    method="GET"
                    action="<?= e($baseUri) ?>manga/recherche"
                    data-base-path="<?= e($baseUri) ?>"
                >

                    <input
                        id="header-search-input"
                        type="search"
                        name="q"
                        placeholder="Rechercher..."
                        value="<?= e($currentSearch) ?>"
                        autocomplete="off"
                    >

                    <button type="submit">
                        🔎
                    </button>

                    <div class="header-search-dropdown js-header-search-dropdown">

                        <div
                            class="header-search-skeleton"
                            aria-hidden="true"
                        >

                            <?php for ($i = 1; $i <= 5; $i++): ?>

                                <div class="header-search-skeleton-item">

                                    <div class="header-search-skeleton-thumb"></div>

                                    <div class="header-search-skeleton-texts">

                                        <div
                                            class="
                                                header-search-skeleton-line
                                                header-search-skeleton-line-title
                                            "
                                        ></div>

                                        <div
                                            class="
                                                header-search-skeleton-line
                                                header-search-skeleton-line-subtitle
                                            "
                                        ></div>

                                    </div>

                                </div>

                            <?php endfor; ?>

                        </div>

                        <div
                            class="header-search-results"
                            id="header-search-results"
                        ></div>

                    </div>

                </form>

            </div>

        <?php else: ?>

            <ul>

                <li>

                    <a
                        class="nav-link-icon"
                        data-prefetch
                        href="<?= e($baseUri) ?>connexion"
                        title="Connexion"
                    >
                        🔐
                    </a>

                </li>

            </ul>

        <?php endif; ?>

    </nav>

</header>