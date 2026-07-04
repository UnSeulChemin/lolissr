<?php

declare(strict_types=1);

use App\DTO\Common\Responses\ViewData;

/** @var ViewData $view */

if (!isset($stats))
{
    throw new \RuntimeException(
        'Stats manquantes dans la vue.',
    );
}

$hasLongestSeries =
    $stats->longestSeries !== null;

$hasLastTome =
    $stats->lastTome !== null;

$hasTopLongestSeries =
    is_iterable($stats->topLongestSeries)
    && count((array) $stats->topLongestSeries) > 0;

$hasLatestArtbook =
    $stats->latestArtbook !== null;

$hasMostRepresented =
    $stats->mostRepresented !== null;

?>

<section class="layout-container">

    <!-- =========================================
         TOP GRID
    ========================================== -->

    <section class="home-grid home-grid-top card-grid-3">

        <!-- Longest Series -->

        <?php if ($hasLongestSeries): ?>

            <?php

            $serie =
                $stats->longestSeries;

            ?>

            <a
                class="
                    card
                    transition-card
                    card-link
                    card-link-wide
                    card-wide
                "
                data-prefetch
                href="<?= e($view->baseUri . $serie->url) ?>"
            >

                <h2 class="home-card-title">
                    📚 Série la plus longue
                </h2>

                <div class="home-longest-content">

                    <div
                        class="
                            card-image-box-portrait
                            home-feature-image-box
                        "
                    >

                        <img
                            class="card-image-portrait"
                            src="<?= e($view->baseUri . $serie->thumbnailUrl) ?>"
                            alt="<?= e($serie->livre) ?>"
                        >

                    </div>

                    <div class="home-longest-info">

                        <p class="home-longest-name">
                            <?= e($serie->livre) ?>
                        </p>

                        <p class="home-longest-count">
                            <?= e($serie->totalLabel) ?>
                        </p>

                    </div>

                </div>

            </a>

        <?php else: ?>

            <article
                class="
                    card
                    transition-card
                    card-wide
                "
            >

                <h2 class="home-card-title">
                    📚 Série la plus longue
                </h2>

                <p class="home-empty">
                    Aucune donnée
                </p>

            </article>

        <?php endif; ?>


        <!-- Last Tome -->

        <?php if ($hasLastTome): ?>

            <?php

            $tome =
                $stats->lastTome;

            ?>

            <a
                class="
                    card
                    transition-card
                    card-link
                    card-medium
                "
                data-prefetch
                href="<?= e($view->baseUri . $tome->url) ?>"
            >

                <h2 class="home-card-title">
                    🆕 Dernier tome ajouté
                </h2>

                <div class="home-feature-content">

                    <div
                        class="
                            card-image-box-portrait
                            home-feature-image-box
                        "
                    >

                        <img
                            class="card-image-portrait"
                            src="<?= e($view->baseUri . $tome->thumbnailUrl) ?>"
                            alt="<?= e($tome->livre) ?>"
                        >

                    </div>

                    <div class="home-feature-info">

                        <p class="home-feature-title">
                            <?= e($tome->livre) ?>
                        </p>

                        <p class="home-feature-meta">
                            <?= e($tome->numeroLabel) ?>
                        </p>

                    </div>

                </div>

            </a>

        <?php else: ?>

            <article
                class="
                    card
                    transition-card
                    card-medium
                "
            >

                <h2 class="home-card-title">
                    🆕 Dernier tome ajouté
                </h2>

                <p class="home-empty">
                    Aucune donnée
                </p>

            </article>

        <?php endif; ?>

    </section>


    <!-- =========================================
         GLOBAL STATS
    ========================================== -->

    <section class="home-grid home-grid-stats card-grid-3">

        <a
            class="
                card
                transition-card
                card-small
                card-link
            "
            data-prefetch
            href="<?= e($view->baseUri . 'manga/series') ?>"
        >

            <h2 class="home-card-title">
                📚 Total tomes
            </h2>

            <p class="home-card-value">
                <?= (int) $stats->totalTomes ?> tomes
            </p>

        </a>

        <a
            class="
                card
                transition-card
                card-small
                card-link
            "
            data-prefetch
            href="<?= e($view->baseUri . 'manga/series') ?>"
        >

            <h2 class="home-card-title">
                📖 Total séries
            </h2>

            <p class="home-card-value">
                <?= (int) $stats->totalSeries ?> séries
            </p>

        </a>

        <a
            class="
                card
                transition-card
                card-small
                card-link
            "
            data-prefetch
            href="<?= e($view->baseUri . 'manga/series/notes') ?>"
        >

            <h2 class="home-card-title">
                ⭐ Note moyenne globale
            </h2>

            <p class="home-card-value">

                <?= e($stats->averageNoteLabel) ?>

            </p>

        </a>

    </section>


    <!-- =========================================
         TOP LONGEST SERIES
    ========================================== -->

    <?php if ($hasTopLongestSeries): ?>

        <h2 class="home-section-title">
            📊 Top 5 séries les plus longues
        </h2>

        <section class="home-ranking-list card-list">

            <?php foreach (
                $stats->topLongestSeries as $index => $serie
            ): ?>

                <a
                    class="
                        card
                        transition-card
                        card-link
                        card-bottom
                    "
                    data-prefetch
                    href="<?= e($view->baseUri . $serie->url) ?>"
                >

                    <p class="home-series-rank">
                        #<?= $index + 1 ?>
                    </p>

                    <div class="card-image-box-portrait">

                        <img
                            class="card-image-portrait"
                            src="<?= e($view->baseUri . $serie->thumbnailUrl) ?>"
                            alt="<?= e($serie->livre) ?>"
                        >

                    </div>

                    <p class="home-list-card-title">
                        <?= e($serie->livre) ?>
                    </p>

                    <p class="home-list-card-meta">
                        <?= e($serie->totalLabel) ?>
                    </p>

                </a>

            <?php endforeach; ?>

        </section>

    <?php endif; ?>


    <!-- =========================================
         READING STATS
    ========================================== -->

    <h2 class="home-section-title">
        📖 Lecture
    </h2>

    <section class="home-grid home-grid-stats card-grid-3">

        <a
            class="
                card
                transition-card
                card-small
                card-link
            "
            data-prefetch
            href="<?= e($view->baseUri . 'manga/series/a-lire') ?>"
        >

            <h2 class="home-card-title">
                ✅ Total lus
            </h2>

            <p class="home-card-value">
                <?= (int) $stats->totalRead ?> lus
            </p>

        </a>

        <a
            class="
                card
                transition-card
                card-small
                card-link
            "
            data-prefetch
            href="<?= e($view->baseUri . 'manga/series/a-lire') ?>"
        >

            <h2 class="home-card-title">
                📚 Restants à lire
            </h2>

            <p class="home-card-value">
                <?= (int) $stats->totalUnread ?> à lire
            </p>

        </a>

        <a
            class="
                card
                transition-card
                card-small
                card-link
                home-reading-progress-card
            "
            data-prefetch
            href="<?= e($view->baseUri . 'manga/series/a-lire') ?>"
        >

            <h2 class="home-card-title">
                📊 Progression lecture
            </h2>

            <p
                class="
                    home-card-value
                    home-reading-progress-value
                "
            >
                <?= (int) $stats->readingProgress ?>%
            </p>

            <div
                class="home-reading-progress"
                style="--progress: <?= (int) $stats->readingProgress ?>%;"
            >

                <div class="home-reading-progress-bar"></div>

            </div>

        </a>

    </section>

    <h2 class="home-section-title">
        🎨 Artbooks
    </h2>

    <section class="home-grid home-grid-top card-grid-3">

        <!-- Auteur / Série le plus représenté -->

        <?php if ($hasMostRepresented): ?>

            <?php $mostRepresented = $stats->mostRepresented; ?>

            <article
                class="
                    card
                    transition-card
                    card-link
                    card-link-wide
                    card-wide
                "
            >

                <h2 class="home-card-title">
                    <?= e($mostRepresented->title) ?>
                </h2>

                <div class="home-longest-content">

                    <div class="card-image-box-portrait home-feature-image-box">

                        <img
                            class="card-image-portrait"
                            src="<?= e($view->baseUri . $mostRepresented->thumbnailUrl) ?>"
                            alt="<?= e($mostRepresented->name) ?>"
                        >

                    </div>

                    <div class="home-longest-info">

                        <p class="home-longest-name">
                            <?= e($mostRepresented->name) ?>
                        </p>

                        <p class="home-longest-count">
                            <?= e($mostRepresented->countLabel) ?>
                        </p>

                    </div>

                </div>

            </article>

        <?php else: ?>

            <article
                class="
                    card
                    transition-card
                    card-wide
                "
            >

                <h2 class="home-card-title">
                    🎨 Artbooks
                </h2>

                <p class="home-empty">
                    Aucune donnée
                </p>

            </article>

        <?php endif; ?>

        <!-- Dernier artbook ajouté -->

        <?php if ($hasLatestArtbook): ?>

            <?php

            $artbook =
                $stats->latestArtbook;

            ?>

            <article
                class="
                    card
                    transition-card
                    card-link
                    card-medium
                "
            >

                <h2 class="home-card-title">
                    🎨 Dernier artbook ajouté
                </h2>

                <div class="home-feature-content">

                    <div
                        class="
                            card-image-box-portrait
                            home-feature-image-box
                        "
                    >

                        <img
                            class="card-image-portrait"
                            src="<?= e($view->baseUri . $artbook->thumbnailUrl) ?>"
                            alt="<?= e($artbook->artbook) ?>"
                        >

                    </div>

                    <div class="home-feature-info">

                        <p class="home-feature-title">
                            <?= e($artbook->artbook) ?>
                        </p>

                        <p class="home-feature-meta">
                            <?= e($artbook->authorLabel) ?>
                        </p>

                    </div>

                </div>

            </article>

        <?php else: ?>

            <article
                class="
                    card
                    transition-card
                    card-medium
                "
            >

                <h2 class="home-card-title">
                    🎨 Dernier artbook ajouté
                </h2>

                <p class="home-empty">
                    Aucune donnée
                </p>

            </article>

        <?php endif; ?>

    </section>

    <section class="home-grid home-grid-stats card-grid-3">

        <a
            class="
                card
                transition-card
                card-small
                card-link
            "
            data-prefetch
            href="<?= e($view->baseUri . 'manga/artbooks') ?>"
        >

            <h2 class="home-card-title">
                📚 Total artbooks
            </h2>

            <p class="home-card-value">
                <?= (int) $stats->totalArtbooks ?>
            </p>

        </a>

        <article class="card transition-card card-small">

            <h2 class="home-card-title">
                📖 Séries représentées
            </h2>

            <p class="home-card-value">
                <?= (int) $stats->totalArtbookSeries ?>
            </p>

        </article>

        <article class="card transition-card card-small">

            <h2 class="home-card-title">
                🎨 Auteurs représentés
            </h2>

            <p class="home-card-value">
                <?= (int) $stats->totalArtbookAuthors ?>
            </p>

        </article>

    </section>

    <h2 class="home-section-title">
        👑 Maîtrise du mandarin
    </h2>

    <section class="home-grid home-grid-stats card-grid-3">

        <article
            class="
                card
                transition-card
                card-small
            "
        >

            <h2 class="home-card-title">
                📚 Total vocabulaires
            </h2>

            <p class="home-card-value">
                <?= (int) $stats->totalVocabulary ?>
            </p>

        </article>

        <article
            class="
                card
                transition-card
                card-small
            "
        >

            <h2 class="home-card-title">
                📖 Total grammaires
            </h2>

            <p class="home-card-value">
                <?= (int) $stats->totalGrammar ?>
            </p>

        </article>

        <article
            class="
                card
                transition-card
                card-small
            "
        >

            <h2 class="home-card-title">
                🏆 Niveau moyen global
            </h2>

            <p class="home-card-value">

                <?= e($stats->globalChineseProgressLabel) ?>

            </p>

        </article>

    </section>

    <h2 class="home-section-title">
        🎓 Maîtrise du chinois
    </h2>

    <section class="home-grid home-grid-stats card-grid-3">

        <!-- VOCABULAIRE -->

        <article class="card transition-card card-small">

            <h2 class="home-card-title">
                📚 Vocabulaire appris
            </h2>

            <p class="home-card-value">
                <?= $stats->learnedVocabulary ?>
            </p>

        </article>

        <article class="card transition-card card-small">

            <h2 class="home-card-title">
                🎯 Vocabulaire restant
            </h2>

            <p class="home-card-value">
                <?= (int) $stats->remainingVocabulary ?>
            </p>

        </article>

        <article
            class="
                card
                transition-card
                card-small
                home-reading-progress-card
            "
        >

            <h2 class="home-card-title">
                📊 Progression vocabulaire
            </h2>

            <p
                class="
                    home-card-value
                    home-reading-progress-value
                "
            >
                <?= (int) $stats->vocabularyProgress ?>%
            </p>

            <div
                class="home-reading-progress"
                style="--progress: <?= (int) $stats->vocabularyProgress ?>%;"
            >
                <div class="home-reading-progress-bar"></div>
            </div>

        </article>


        <!-- GRAMMAIRE -->

        <article class="card transition-card card-small">

            <h2 class="home-card-title">
                📖 Grammaire apprise
            </h2>

            <p class="home-card-value">
                <?= $stats->learnedGrammar ?>
            </p>

        </article>

        <article class="card transition-card card-small">

            <h2 class="home-card-title">
                🎯 Grammaire restante
            </h2>

            <p class="home-card-value">
                <?= (int) $stats->remainingGrammar ?>
            </p>

        </article>

        <article
            class="
                card
                transition-card
                card-small
                home-reading-progress-card
            "
        >

            <h2 class="home-card-title">
                📊 Progression grammaire
            </h2>

            <p
                class="
                    home-card-value
                    home-reading-progress-value
                "
            >
                <?= (int) $stats->grammarProgress ?>%
            </p>

            <div
                class="home-reading-progress"
                style="--progress: <?= (int) $stats->grammarProgress ?>%;"
            >
                <div class="home-reading-progress-bar"></div>
            </div>

        </article>

    </section>

</section>