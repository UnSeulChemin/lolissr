<section class="layout-container">

    <section class="home-grid home-grid-top card-grid-3 animate-fade-up-stagger">

        <?php if (!empty($stats->longestSeries)): ?>
            <a class="card card-link card-link-wide card-wide"
               href="<?= $basePath; ?>manga/series/<?= rawurlencode($stats->longestSeries->slug) ?>">

                <h2 class="home-card-title">📚 Série la plus longue</h2>

                <div class="home-longest-content">

                    <div class="card-image-box-portrait home-feature-image-box">
                        <img
                            class="card-image-portrait"
                            src="<?= $basePath; ?>public/images/mangas/thumbnail/<?= htmlspecialchars($stats->longestSeries->thumbnail . '.' . $stats->longestSeries->extension) ?>"
                            alt="<?= htmlspecialchars($stats->longestSeries->livre) ?>">
                    </div>

                    <div class="home-longest-info">
                        <p class="home-longest-name">
                            <?= htmlspecialchars($stats->longestSeries->livre) ?>
                        </p>

                        <p class="home-longest-count">
                            <?= (int) $stats->longestSeries->total ?> tomes
                        </p>
                    </div>

                </div>
            </a>
        <?php else: ?>
            <article class="card card-wide">
                <h2 class="home-card-title">📚 Série la plus longue</h2>
                <p class="home-empty">Aucune donnée</p>
            </article>
        <?php endif; ?>


        <?php if (!empty($stats->lastTome)): ?>
            <a class="card card-link card-medium"
               href="<?= $basePath; ?>manga/series/<?= rawurlencode($stats->lastTome->slug) ?>/<?= (int) $stats->lastTome->numero ?>">

                <h2 class="home-card-title">🆕 Dernier tome ajouté</h2>

                <div class="home-feature-content">

                    <div class="card-image-box-portrait home-feature-image-box">
                        <img
                            class="card-image-portrait"
                            src="<?= $basePath; ?>public/images/mangas/thumbnail/<?= htmlspecialchars($stats->lastTome->thumbnail . '.' . $stats->lastTome->extension) ?>"
                            alt="<?= htmlspecialchars($stats->lastTome->livre) ?>">
                    </div>

                    <div class="home-feature-info">
                        <p class="home-feature-title">
                            <?= htmlspecialchars($stats->lastTome->livre) ?>
                        </p>

                        <p class="home-feature-meta">
                            Tome <?= str_pad((string) $stats->lastTome->numero, 2, '0', STR_PAD_LEFT) ?>
                        </p>
                    </div>

                </div>
            </a>
        <?php else: ?>
            <article class="card card-medium">
                <h2 class="home-card-title">🆕 Dernier tome ajouté</h2>
                <p class="home-empty">Aucune donnée</p>
            </article>
        <?php endif; ?>

    </section>

    <!-- STATS -->
    <section class="home-grid home-grid-stats card-grid-3 animate-fade-up-stagger">

        <article class="card card-small">
            <h2 class="home-card-title">📚 Total tomes</h2>
            <p class="home-card-value"><?= (int) $stats->totalTomes ?> tomes</p>
        </article>

        <article class="card card-small">
            <h2 class="home-card-title">📖 Total séries</h2>
            <p class="home-card-value"><?= (int) $stats->totalSeries ?> séries</p>
        </article>

        <article class="card card-small">
            <h2 class="home-card-title">⭐ Note moyenne globale</h2>

            <p class="home-card-value">
                <?= $stats->averageNote !== null
                    ? number_format((float) $stats->averageNote, 1, ',', ' ') . '/10'
                    : 'Aucune note' ?>
            </p>
        </article>

    </section>


    <!-- TOP SERIES -->
    <?php if (!empty($stats->topLongestSeries)): ?>
        <h2 class="home-section-title">📊 Top 5 séries les plus longues</h2>

        <section class="home-ranking-list card-list animate-fade-up-stagger">

            <?php foreach ($stats->topLongestSeries as $index => $serie): ?>
                <a class="card card-link card-bottom"
                   href="<?= $basePath; ?>manga/series/<?= rawurlencode($serie->slug) ?>">

                    <p class="home-series-rank">#<?= $index + 1 ?></p>

                    <div class="card-image-box-portrait">
                        <img
                            class="card-image-portrait"
                            src="<?= $basePath; ?>public/images/mangas/thumbnail/<?= htmlspecialchars($serie->thumbnail . '.' . $serie->extension) ?>"
                            alt="<?= htmlspecialchars($serie->livre) ?>">
                    </div>

                    <p class="home-list-card-title">
                        <?= htmlspecialchars($serie->livre) ?>
                    </p>

                    <p class="home-list-card-meta">
                        <?= (int) $serie->total ?> tomes
                    </p>

                </a>
            <?php endforeach; ?>

        </section>
    <?php endif; ?>


    <!-- LECTURE -->
    <h2 class="home-section-title">📖 Lecture</h2>

    <section class="home-grid home-grid-stats card-grid-3 animate-fade-up-stagger">

        <article class="card card-small">
            <h2 class="home-card-title">✅ Total lus</h2>
            <p class="home-card-value"><?= (int) $stats->totalRead ?> lus</p>
        </article>

        <article class="card card-small">
            <h2 class="home-card-title">📚 Restants à lire</h2>
            <p class="home-card-value"><?= (int) $stats->totalUnread ?> à lire</p>
        </article>

        <article class="card card-small home-reading-progress-card">
            <h2 class="home-card-title">📊 Progression lecture</h2>

            <p class="home-card-value home-reading-progress-value">
                <?= (int) $stats->readingProgress ?>%
            </p>

            <div class="home-reading-progress"
                 style="--progress: <?= (int) $stats->readingProgress ?>%;">
                <div class="home-reading-progress-bar"></div>
            </div>

        </article>

    </section>


    <!-- WARNING -->
    <h2 class="home-section-title">⚠️ Mangas à surveiller</h2>

    <?php
        $lowGlobal = $stats->lowRatedMangas[0] ?? null;
        $lowJacquette = $stats->lowJacquetteMangas[0] ?? null;
        $lowLivre = $stats->lowLivreStateMangas[0] ?? null;
    ?>

    <section class="home-grid home-grid-watch card-grid-3 animate-fade-up-stagger">

        <?php if ($lowGlobal): ?>
            <a class="card card-link card-medium"
               href="<?= $basePath; ?>manga/series/<?= rawurlencode($lowGlobal->slug) ?>/<?= (int) $lowGlobal->numero ?>">

                <h2 class="home-card-title">📉 À remplacer</h2>

                <div class="home-feature-content">
                    <div class="card-image-box-portrait home-feature-image-box">
                        <img
                            class="card-image-portrait"
                            src="<?= $basePath; ?>public/images/mangas/thumbnail/<?= htmlspecialchars($lowGlobal->thumbnail . '.' . $lowGlobal->extension) ?>"
                            alt="<?= htmlspecialchars($lowGlobal->livre) ?>">
                    </div>

                    <div class="home-feature-info">
                        <p><?= htmlspecialchars($lowGlobal->livre) ?></p>
                        <p><?= (int) $lowGlobal->note ?>/10</p>
                    </div>
                </div>
            </a>
        <?php endif; ?>


        <?php if ($lowJacquette): ?>
            <a class="card card-link card-medium"
               href="<?= $basePath; ?>manga/series/<?= rawurlencode($lowJacquette->slug) ?>/<?= (int) $lowJacquette->numero ?>">

                <h2 class="home-card-title">🧥 Jacquette faible</h2>

                <p><?= htmlspecialchars($lowJacquette->livre) ?></p>
                <p><?= (int) $lowJacquette->jacquette ?>/5</p>

            </a>
        <?php endif; ?>


        <?php if ($lowLivre): ?>
            <a class="card card-link card-medium"
               href="<?= $basePath; ?>manga/series/<?= rawurlencode($lowLivre->slug) ?>/<?= (int) $lowLivre->numero ?>">

                <h2 class="home-card-title">📘 Livre abîmé</h2>

                <p><?= htmlspecialchars($lowLivre->livre) ?></p>
                <p><?= (int) $lowLivre->livre_note ?>/5</p>

            </a>
        <?php endif; ?>

    </section>


    <!-- LOW RATED -->
    <h2 class="home-section-title">📉 Mangas les moins bien notés</h2>

    <?php if (!empty($stats->lowRatedMangas)): ?>
        <section class="home-low-rated-list card-list animate-fade-up-stagger">

            <?php foreach ($stats->lowRatedMangas as $manga): ?>
                <a class="card card-link card-bottom"
                   href="<?= $basePath; ?>manga/series/<?= rawurlencode($manga->slug) ?>/<?= (int) $manga->numero ?>">

                    <span class="home-note-badge">
                        ⭐ <?= (int) $manga->note ?>/10
                    </span>

                    <div class="card-image-box-portrait">
                        <img
                            class="card-image-portrait"
                            src="<?= $basePath; ?>public/images/mangas/thumbnail/<?= htmlspecialchars($manga->thumbnail . '.' . $manga->extension) ?>"
                            alt="<?= htmlspecialchars($manga->livre) ?>">
                    </div>

                    <p class="home-list-card-title">
                        <?= htmlspecialchars($manga->livre) ?>
                    </p>

                    <p class="home-list-card-meta">
                        Tome <?= str_pad((string) $manga->numero, 2, '0', STR_PAD_LEFT) ?>
                    </p>

                </a>
            <?php endforeach; ?>

        </section>
    <?php endif; ?>

</section>