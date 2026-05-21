<?php

declare(strict_types=1);

$mangas = isset($view['mangas'])
    && is_array($view['mangas'])
        ? $view['mangas']
        : [];

$compteur = isset($view['compteur'])
    ? (int) $view['compteur']
    : 0;

$currentPage = isset($view['currentPage'])
    ? (int) $view['currentPage']
    : 1;

$slugFilter = $view['slugFilter'] ?? null;

$isSerieView = is_string($slugFilter)
    && trim($slugFilter) !== '';

$basePath = rtrim(
    (string) ($basePath ?? ''),
    '/',
) . '/';

?>

<section class="layout-container dashboard-page">

    <div class="collection-ajax-container is-loading">

        <div
            class="collection-scroll-anchor"
            aria-hidden="true"></div>

        <div
            class="collection-skeleton"
            aria-hidden="true">

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
                        $slug = (string) ($manga->slug ?? '');
                        $numero = (int) ($manga->numero ?? 0);
                        $livre = (string) ($manga->livre ?? '');

                        $thumbnail = $manga->thumbnail ?? null;
                        $extension = $manga->extension ?? null;

                        $statut = (string) (
                            $manga->statut
                            ?? 'en_cours'
                        );

                        $note = $manga->note ?? null;

                        $averageNote = $manga->averageNote
                            ?? $manga->average_note
                            ?? null;

                        $total = (int) (
                            $manga->total
                            ?? 0
                        );

                        $totalLu = (int) (
                            $manga->totalLu
                            ?? $manga->total_lu
                            ?? 0
                        );

                        $lu = (int) (
                            $manga->lu
                            ?? 0
                        );

                        if (
                            $slug === ''
                            || $livre === ''
                            || $thumbnail === null
                            || $extension === null
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

                        $displayNote = $isSerieView
                            ? $note
                            : $averageNote;

                        $noteClass = 'collection-note-mid';

                        if ($displayNote !== null) {
                            $displayNote = (float) $displayNote;

                            if ($displayNote >= 8) {
                                $noteClass = 'collection-note-good';
                            } elseif ($displayNote <= 4) {
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

                        $noteLabel = '0';

                        if ($displayNote !== null) {
                            $noteLabel = $isSerieView
                                ? (string) (int) $displayNote
                                : number_format(
                                    $displayNote,
                                    1,
                                    ',',
                                    '',
                                );
                        }

                        $statutLabel = $statut === 'termine'
                            ? 'Terminé'
                            : 'En cours';

                        $statutClass = $statut === 'termine'
                            ? 'collection-status-finished'
                            : 'collection-status-progress';

                        $thumbnailPath = $basePath
                            . 'public/images/mangas/thumbnail/'
                            . $thumbnail
                            . '.'
                            . $extension;
                        ?>

                        <a
                            class="card card-link collection-card-link"
                            href="<?= e($href) ?>">

                            <?php if (!$isSerieView): ?>

                                <span
                                    class="collection-status-badge <?= e($statutClass) ?>">

                                    <?= e($statutLabel) ?>

                                </span>

                            <?php endif; ?>

                            <span
                                class="collection-card-badge <?= e($noteClass) ?>">

                                ⭐ <?= e($noteLabel) ?>/10

                            </span>

                            <span
                                class="collection-read-badge <?= $readBadgeActive ? 'active' : '' ?>"
                                title="<?= e($readBadgeTitle) ?>"
                                aria-label="<?= e($readBadgeTitle) ?>">

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
                                    src="<?= e($thumbnailPath) ?>"
                                    alt="<?= e($livre) ?>">

                            </div>

                            <p class="collection-card-title">

                                <?= e($livre) ?>

                            </p>

                            <p class="collection-card-subtitle">

                                <?php if ($isSerieView): ?>

                                    Tome <?= str_pad(
                                        (string) $numero,
                                        2,
                                        '0',
                                        STR_PAD_LEFT,
                                    ) ?>

                                <?php else: ?>

                                    <?= $total ?> tomes

                                <?php endif; ?>

                            </p>

                        </a>

                    <?php endforeach; ?>

                </section>

            <?php endif; ?>

            <?php if (
                !$isSerieView
                && $compteur > 1
            ): ?>

                <nav class="collection-pagination">

                    <?php for (
                        $page = 1;
                        $page <= $compteur;
                        $page++
                    ): ?>

                        <a
                            class="collection-pagination-link <?= $currentPage === $page ? 'active' : '' ?>"
                            href="<?= e($basePath) ?>manga/series/page/<?= $page ?>">

                            <?= $page ?>

                        </a>

                    <?php endfor; ?>

                </nav>

            <?php endif; ?>

            <?php if ($isSerieView): ?>

                <div class="collection-back-wrapper">

                    <a
                        class="form-submit collection-back-button"
                        href="<?= e($basePath) ?>manga/series">

                        Retour

                    </a>

                </div>

            <?php endif; ?>

        </div>

    </div>

</section>