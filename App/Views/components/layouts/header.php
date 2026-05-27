<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Base URI
|--------------------------------------------------------------------------
*/

$baseUri = rtrim(
    (string) ($baseUri ?? ''),
    '/',
) . '/';

/*
|--------------------------------------------------------------------------
| Current Search
|--------------------------------------------------------------------------
*/

$currentSearch =
    isset($currentSearch)
        ? (string) $currentSearch
        : '';

?>

<header>

    <nav>

        <!-- =====================================
             Logo
        ====================================== -->

        <a
            class="site-logo"
            href="<?= e($baseUri) ?>"
            title="Accueil"
        >

            <span class="site-logo-loli">
                Loli
            </span>

            <span class="site-logo-ssr">
                SSR
            </span>

        </a>

        <!-- =====================================
             Navigation
        ====================================== -->

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

        </ul>

        <!-- =====================================
             Search
        ====================================== -->

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
                    aria-label="Rechercher"
                    autocomplete="off"
                >

                <button
                    type="submit"
                    title="Rechercher"
                    aria-label="Rechercher"
                >

                    🔎

                </button>

                <!-- =================================
                     Dropdown
                ================================== -->

                <div class="header-search-dropdown js-header-search-dropdown">

                    <!-- =============================
                         Skeleton
                    ============================== -->

                    <div
                        class="header-search-skeleton"
                        aria-hidden="true"
                    >

                        <?php for (
                            $i = 1;
                            $i <= 5;
                            $i++
                        ): ?>

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

                    <!-- =============================
                         Results
                    ============================== -->

                    <div
                        class="header-search-results"
                        id="header-search-results"
                    ></div>

                </div>

            </form>

        </div>

    </nav>

</header>