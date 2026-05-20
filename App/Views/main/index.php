<?php

declare(strict_types=1);

$stats = $view['stats'] ?? null;

if ($stats === null) {
    throw new \RuntimeException(
        'Stats manquantes dans la vue.'
    );
}

$basePath = rtrim(
    (string) ($basePath ?? ''),
    '/'
) . '/';

$hasLongestSeries = $stats->longestSeries !== null;

$hasLastTome = $stats->lastTome !== null;

$hasTopLongestSeries = is_iterable($stats->topLongestSeries)
    && count((array) $stats->topLongestSeries) > 0;

?>

<section class="layout-container">

    <section class="home-grid home-grid-top card-grid-3 animate-fade-up-stagger">

        <?php if ($hasLongestSeries): ?>

            <?php
            $serie = $stats->longestSeries;

            $href = $basePath
                . 'manga/series/'
                . rawurlencode((string) $serie->slug);

            $thumbnailPath = $basePath
                . 'public/images/mangas/thumbnail/'
                . $serie->thumbnail
                . '.'
                . $serie->extension;
            ?>

            <a
                class="card card-link card-link-wide card-wide"
                href="<?= e($href) ?>">

                <h2 class="home-card-title">
                    📚 Série la plus longue
                </h2>

                <div class="home-longest-content">

                    <div class="card-image-box-portrait home-feature-image-box">

                        <img
                            class="card-image-portrait"
                            src="<?= e($thumbnailPath) ?>"
                            alt="<?= e($serie->livre) ?>">

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

            <article class="card card-wide">

                <h2 class="home-card-title">
                    📚 Série la plus longue
                </h2>

                <p class="home-empty">
                    Aucune donnée
                </p>

            </article>

        <?php endif; ?>

        <?php if ($hasLastTome): ?>

            <?php
            $tome = $stats->lastTome;

            $href = $basePath
                . 'manga/series/'
                . rawurlencode((string) $tome->slug)
                . '/'
                . (int) $tome->numero;

            $thumbnailPath = $basePath
                . 'public/images/mangas/thumbnail/'
                . $tome->thumbnail
                . '.'
                . $tome->extension;
            ?>

            <a
                class="card card-link card-medium"
                href="<?= e($href) ?>">

                <h2 class="home-card-title">
                    🆕 Dernier tome ajouté
                </h2>

                <div class="home-feature-content">

                    <div class="card-image-box-portrait home-feature-image-box">

                        <img
                            class="card-image-portrait"
                            src="<?= e($thumbnailPath) ?>"
                            alt="<?= e($tome->livre) ?>">

                    </div>

                    <div class="home-feature-info">

                        <p class="home-feature-title">
                            <?= e($tome->livre) ?>
                        </p>

                        <p class="home-feature-meta">

                            Tome <?= str_pad(
                                (string) $tome->numero,
                                2,
                                '0',
                                STR_PAD_LEFT
                            ) ?>

                        </p>

                    </div>

                </div>

            </a>

        <?php else: ?>

            <article class="card card-medium">

                <h2 class="home-card-title">
                    🆕 Dernier tome ajouté
                </h2>

                <p class="home-empty">
                    Aucune donnée
                </p>

            </article>

        <?php endif; ?>

    </section>

    <section class="home-grid home-grid-stats card-grid-3 animate-fade-up-stagger">

        <article class="card card-small">

            <h2 class="home-card-title">
                📚 Total tomes
            </h2>

            <p class="home-card-value">
                <?= (int) $stats->totalTomes ?> tomes
            </p>

        </article>

        <article class="card card-small">

            <h2 class="home-card-title">
                📖 Total séries
            </h2>

            <p class="home-card-value">
                <?= (int) $stats->totalSeries ?> séries
            </p>

        </article>

        <article class="card card-small">

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
                            ' '
                        ) . '/10'
                    )
                    : 'Aucune note' ?>

            </p>

        </article>

    </section>

    <?php if ($hasTopLongestSeries): ?>

        <h2 class="home-section-title">
            📊 Top 5 séries les plus longues
        </h2>

        <section class="home-ranking-list card-list animate-fade-up-stagger">

            <?php foreach ($stats->topLongestSeries as $index => $serie): ?>

                <?php
                $href = $basePath
                    . 'manga/series/'
                    . rawurlencode((string) $serie->slug);

                $thumbnailPath = $basePath
                    . 'public/images/mangas/thumbnail/'
                    . $serie->thumbnail
                    . '.'
                    . $serie->extension;
                ?>

                <a
                    class="card card-link card-bottom"
                    href="<?= e($href) ?>">

                    <p class="home-series-rank">
                        #<?= $index + 1 ?>
                    </p>

                    <div class="card-image-box-portrait">

                        <img
                            class="card-image-portrait"
                            src="<?= e($thumbnailPath) ?>"
                            alt="<?= e($serie->livre) ?>">

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

    <h2 class="home-section-title">
        📖 Lecture
    </h2>

    <section class="home-grid home-grid-stats card-grid-3 animate-fade-up-stagger">

        <article class="card card-small">

            <h2 class="home-card-title">
                ✅ Total lus
            </h2>

            <p class="home-card-value">
                <?= (int) $stats->totalRead ?> lus
            </p>

        </article>

        <article class="card card-small">

            <h2 class="home-card-title">
                📚 Restants à lire
            </h2>

            <p class="home-card-value">
                <?= (int) $stats->totalUnread ?> à lire
            </p>

        </article>

        <article class="card card-small home-reading-progress-card">

            <h2 class="home-card-title">
                📊 Progression lecture
            </h2>

            <p class="home-card-value home-reading-progress-value">
                <?= (int) $stats->readingProgress ?>%
            </p>

            <div
                class="home-reading-progress"
                style="--progress: <?= (int) $stats->readingProgress ?>%;">

                <div class="home-reading-progress-bar"></div>

            </div>

        </article>

    </section>

</section>