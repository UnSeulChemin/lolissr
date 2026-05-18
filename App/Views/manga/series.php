<?php

$mangas = isset($mangas) && is_array($mangas)
    ? $mangas
    : [];

$compteur = isset($compteur)
    ? (int) $compteur
    : 0;

$currentPage = isset($currentPage)
    ? (int) $currentPage
    : 1;

$slugFilter = $slugFilter ?? null;

$isSerieView = is_string($slugFilter)
    && trim($slugFilter) !== '';

$basePath = rtrim($basePath, '/') . '/';

?>

<section class="layout-container dashboard-page">

    <div class="collection-ajax-container is-loading">

        <div class="collection-scroll-anchor" aria-hidden="true"></div>

        <div class="collection-skeleton" aria-hidden="true">

            <?php for ($i = 1; $i <= 8; $i++): ?>

                <article class="collection-skeleton-card">

                    <div class="collection-skeleton-image"></div>

                    <div class="collection-skeleton-line collection-skeleton-line-title"></div>

                    <div class="collection-skeleton-line collection-skeleton-line-subtitle"></div>

                </article>

            <?php endfor; ?>

        </div>

        <div class="collection-ajax-content">

            <?php if ($mangas === []): ?>

                <p class="collection-empty">
                    Aucun manga trouvé.
                </p>

            <?php else: ?>

                <section class="collection-grid animate-fade-up-stagger">

                    <?php foreach ($mangas as $manga): ?>

                        <?php
                        $slug = isset($manga->slug)
                            ? (string) $manga->slug
                            : '';

                        $numero = isset($manga->numero)
                            ? (int) $manga->numero
                            : 0;

                        $thumbnail = isset($manga->thumbnail)
                            ? (string) $manga->thumbnail
                            : '';

                        $extension = isset($manga->extension)
                            ? (string) $manga->extension
                            : '';

                        $livre = isset($manga->livre)
                            ? (string) $manga->livre
                            : '';

                        $statut = isset($manga->statut)
                            ? (string) $manga->statut
                            : 'en_cours';

                        $note = $isSerieView
                            ? ($manga->note ?? null)
                            : ($manga->average_note ?? null);

                        $total = isset($manga->total)
                            ? (int) $manga->total
                            : 0;

                        $totalLu = isset($manga->total_lu)
                            ? (int) $manga->total_lu
                            : 0;

                        $lu = isset($manga->lu)
                            ? (int) $manga->lu
                            : 0;

                        if (
                            $slug === ''
                            || $thumbnail === ''
                            || $extension === ''
                            || $livre === ''
                        ) {
                            continue;
                        }

                        $href = $isSerieView
                            ? $basePath
                                . 'manga/series/'
                                . rawurlencode($slug)
                                . '/'
                                . $numero
                            : $basePath
                                . 'manga/series/'
                                . rawurlencode($slug);

                        $noteClass = 'collection-note-mid';

                        if ($note !== null)
                        {
                            $noteValue = (float) $note;

                            if ($noteValue >= 8)
                            {
                                $noteClass = 'collection-note-good';
                            }
                            elseif ($noteValue <= 4)
                            {
                                $noteClass = 'collection-note-low';
                            }
                        }

                        $readBadgeActive = $isSerieView
                            ? $lu === 1
                            : (
                                $total > 0
                                && $totalLu >= $total
                            );

                        $readBadgeTitle = $isSerieView
                            ? (
                                $readBadgeActive
                                    ? 'Tome lu'
                                    : 'Tome non lu'
                            )
                            : (
                                $readBadgeActive
                                    ? 'Série lue'
                                    : 'Série non terminée'
                            );

                        $noteLabel = $isSerieView
                            ? (string) (int) $note
                            : number_format(
                                (float) $note,
                                1,
                                ',',
                                ''
                            );

                        $statutLabel = $statut === 'termine'
                            ? 'Terminé'
                            : 'En cours';

                        $statutClass = $statut === 'termine'
                            ? 'collection-status-finished'
                            : 'collection-status-progress';
                        ?>

                        <a
                            class="card card-link collection-card-link"
                            href="<?= htmlspecialchars($href, ENT_QUOTES, 'UTF-8'); ?>">

                            <?php if (!$isSerieView): ?>

                                <span class="collection-status-badge <?= htmlspecialchars($statutClass, ENT_QUOTES, 'UTF-8'); ?>">

                                    <?= htmlspecialchars($statutLabel, ENT_QUOTES, 'UTF-8'); ?>

                                </span>

                            <?php endif; ?>

                            <span class="collection-card-badge <?= htmlspecialchars($noteClass, ENT_QUOTES, 'UTF-8'); ?>">

                                ⭐ <?= $note !== null
                                    ? htmlspecialchars($noteLabel, ENT_QUOTES, 'UTF-8')
                                    : '0'; ?>/10

                            </span>

                            <span
                                class="collection-read-badge <?= $readBadgeActive ? 'active' : '' ?>"
                                title="<?= htmlspecialchars($readBadgeTitle, ENT_QUOTES, 'UTF-8'); ?>"
                                aria-label="<?= htmlspecialchars($readBadgeTitle, ENT_QUOTES, 'UTF-8'); ?>">

                                <svg
                                    class="collection-read-icon"
                                    viewBox="0 0 24 24"
                                    aria-hidden="true">

                                    <path d="M7 3C6.45 3 6 3.45 6 4V21L12 17L18 21V4C18 3.45 17.55 3 17 3H7Z"/>

                                </svg>

                            </span>

                            <div class="card-image-box-portrait">

                                <img
                                    class="card-image-portrait"
                                    src="<?= $basePath; ?>public/images/mangas/thumbnail/<?= htmlspecialchars($thumbnail . '.' . $extension, ENT_QUOTES, 'UTF-8'); ?>"
                                    alt="<?= htmlspecialchars($livre, ENT_QUOTES, 'UTF-8'); ?>">

                            </div>

                            <p class="collection-card-title">

                                <?= htmlspecialchars($livre, ENT_QUOTES, 'UTF-8'); ?>

                            </p>

                            <p class="collection-card-subtitle">

                                <?php if ($isSerieView): ?>

                                    Tome <?= str_pad((string) $numero, 2, '0', STR_PAD_LEFT); ?>

                                <?php else: ?>

                                    <?= $total; ?> tomes

                                <?php endif; ?>

                            </p>

                        </a>

                    <?php endforeach; ?>

                </section>

            <?php endif; ?>

            <?php if (!$isSerieView && $compteur > 1): ?>

                <nav class="collection-pagination">

                    <?php for ($getId = 1; $getId <= $compteur; $getId++): ?>

                        <a
                            class="collection-pagination-link <?= ($currentPage === $getId) ? 'active' : ''; ?>"
                            href="<?= $basePath; ?>manga/series/page/<?= $getId; ?>">

                            <?= $getId; ?>

                        </a>

                    <?php endfor; ?>

                </nav>

            <?php endif; ?>

            <?php if ($isSerieView): ?>

                <div class="collection-back-wrapper">

                    <a
                        class="form-submit collection-back-button"
                        href="<?= $basePath; ?>manga/series">

                        Retour

                    </a>

                </div>

            <?php endif; ?>

        </div>

    </div>

</section>