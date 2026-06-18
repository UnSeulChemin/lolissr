<?php

declare(strict_types=1);

if (!isset($stats))
{

    throw new \RuntimeException(
        'Stats manquantes dans la vue.',
    );
}

$baseUri =
    rtrim(
        (string) ($baseUri ?? ''),
        '/',
    ) . '/';

$hasLongestSeries =
    $stats->longestSeries !== null;

$hasLastTome =
    $stats->lastTome !== null;

$hasTopLongestSeries =
    is_iterable($stats->topLongestSeries)
    && count((array) $stats->topLongestSeries) > 0;

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

            $href =
                $baseUri
                . 'manga/series/'
                . rawurlencode(
                    (string) $serie->slug,
                );

            $thumbnailPath =
                $baseUri
                . 'images/mangas/thumbnail/'
                . $serie->thumbnail
                . '.'
                . $serie->extension;

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
                href="<?= e($href) ?>"
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
                            src="<?= e($thumbnailPath) ?>"
                            alt="<?= e($serie->livre) ?>"
                        >

                    </div>

                    <div class="home-longest-info">

                        <p class="home-longest-name">
                            <?= e($serie->livre) ?>
                        </p>

                        <p class="home-longest-count">
                            <?= (int) $serie->total ?> tomes
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

            $href =
                $baseUri
                . 'manga/series/'
                . rawurlencode(
                    (string) $tome->slug,
                )
                . '/'
                . (int) $tome->numero;

            $thumbnailPath =
                $baseUri
                . 'images/mangas/thumbnail/'
                . $tome->thumbnail
                . '.'
                . $tome->extension;

            ?>

            <a
                class="
                    card
                    transition-card
                    card-link
                    card-medium
                "
                data-prefetch
                href="<?= e($href) ?>"
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
                            src="<?= e($thumbnailPath) ?>"
                            alt="<?= e($tome->livre) ?>"
                        >

                    </div>

                    <div class="home-feature-info">

                        <p class="home-feature-title">
                            <?= e($tome->livre) ?>
                        </p>

                        <p class="home-feature-meta">

                            Tome
                            <?= str_pad(
                                (string) $tome->numero,
                                2,
                                '0',
                                STR_PAD_LEFT,
                            ) ?>

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
            href="<?= e($baseUri . 'manga/series') ?>"
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
            href="<?= e($baseUri . 'manga/series') ?>"
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
            href="<?= e($baseUri . 'manga/series/notes') ?>"
        >

            <h2 class="home-card-title">
                ⭐ Note moyenne globale
            </h2>

            <p class="home-card-value">

                <?= $stats->averageNote !== null
                    ? e(
                        number_format(
                            (float) $stats->averageNote,
                            1,
                            ',',
                            ' ',
                        ) . '/10',
                    )
                    : 'Aucune note'
                ?>

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

                <?php

                $href =
                    $baseUri
                    . 'manga/series/'
                    . rawurlencode(
                        (string) $serie->slug,
                    );

                $thumbnailPath =
                    $baseUri
                    . 'images/mangas/thumbnail/'
                    . $serie->thumbnail
                    . '.'
                    . $serie->extension;

                ?>

                <a
                    class="
                        card
                        transition-card
                        card-link
                        card-bottom
                    "
                    data-prefetch
                    href="<?= e($href) ?>"
                >

                    <p class="home-series-rank">
                        #<?= $index + 1 ?>
                    </p>

                    <div class="card-image-box-portrait">

                        <img
                            class="card-image-portrait"
                            src="<?= e($thumbnailPath) ?>"
                            alt="<?= e($serie->livre) ?>"
                        >

                    </div>

                    <p class="home-list-card-title">
                        <?= e($serie->livre) ?>
                    </p>

                    <p class="home-list-card-meta">
                        <?= (int) $serie->total ?> tomes
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

        <article
            class="
                card
                transition-card
                card-small
            "
        >

            <h2 class="home-card-title">
                ✅ Total lus
            </h2>

            <p class="home-card-value">
                <?= (int) $stats->totalRead ?> lus
            </p>

        </article>

        <a
            class="
                card
                transition-card
                card-small
                card-link
            "
            data-prefetch
            href="<?= e($baseUri . 'manga/series/a-lire') ?>"
        >

            <h2 class="home-card-title">
                📚 Restants à lire
            </h2>

            <p class="home-card-value">
                <?= (int) $stats->totalUnread ?> à lire
            </p>

        </a>

        <article
            class="
                card
                transition-card
                card-small
                home-reading-progress-card
            "
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

                <?= number_format(
                    $stats->globalChineseProgress / 10,
                    1,
                    ',',
                    ' ',
                ) ?>/10

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
                <?= (int) (
                    $stats->totalVocabulary
                    - $stats->remainingVocabulary
                ) ?>
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
                <?= (int) (
                    $stats->totalGrammar
                    - $stats->remainingGrammar
                ) ?>
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