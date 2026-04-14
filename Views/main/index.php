<section class="layout-container">

    <section class="home-grid home-grid animate-fade-up-stagger home-grid-top card-grid-3">

        <?php if (!empty($longestSeries)): ?>
            <a
                class="card-link card-link-wide"
                href="<?= $basePath; ?>manga/serie/<?= rawurlencode($longestSeries->slug) ?>">

                <article class="card card-wide">
                    <h2 class="home-card-title">
                        📚 Série la plus longue
                    </h2>

                    <div class="home-longest-content">
                        <img
                            class="home-longest-image card-image"
                            src="<?= $basePath; ?>public/images/mangas/thumbnail/<?= htmlspecialchars($longestSeries->thumbnail . '.' . $longestSeries->extension) ?>"
                            alt="<?= htmlspecialchars($longestSeries->livre) ?>">

                        <div class="home-longest-info">
                            <p class="home-longest-name">
                                <?= htmlspecialchars($longestSeries->livre) ?>
                            </p>

                            <p class="home-longest-count">
                                <?= (int) $longestSeries->total ?> tomes
                            </p>
                        </div>
                    </div>
                </article>
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

        <?php if (!empty($lastTome)): ?>
            <a
                class="card-link"
                href="<?= $basePath; ?>manga/<?= rawurlencode($lastTome->slug) ?>/<?= (int) $lastTome->numero ?>">

                <article class="card card-medium">
                    <h2 class="home-card-title">
                        🆕 Dernier tome ajouté
                    </h2>

                    <div class="home-last-content">
                        <img
                            class="home-last-image card-image"
                            src="<?= $basePath; ?>public/images/mangas/thumbnail/<?= htmlspecialchars($lastTome->thumbnail . '.' . $lastTome->extension) ?>"
                            alt="<?= htmlspecialchars($lastTome->livre) ?>">

                        <div class="home-last-info">
                            <p class="home-last-name">
                                <?= htmlspecialchars($lastTome->livre) ?>
                            </p>

                            <p class="home-last-volume">
                                Tome <?= str_pad((string) $lastTome->numero, 2, '0', STR_PAD_LEFT) ?>
                            </p>
                        </div>
                    </div>
                </article>
            </a>
        <?php endif; ?>

    </section>

    <section class="home-grid home-grid-stats card-grid-3">

        <article class="card card-small">
            <h2 class="home-card-title">
                📚 Total tomes
            </h2>

            <p class="home-card-value">
                <?= (int) $totalTomes ?> tomes
            </p>
        </article>

        <article class="card card-small">
            <h2 class="home-card-title">
                📖 Total séries
            </h2>

            <p class="home-card-value">
                <?= (int) $totalSeries ?> séries
            </p>
        </article>

        <article class="card card-small">
            <h2 class="home-card-title">
                ✨ Note moyenne globale
            </h2>

            <p class="home-card-value">
                <?= $averageNote !== null ? number_format((float) $averageNote, 1, ',', ' ') . '/10' : 'Aucune note' ?>
            </p>
        </article>

    </section>

    <?php if (!empty($topLongestSeries)): ?>
        <h2 class="home-section-title">
            📊 Top 5 séries les plus longues
        </h2>

        <section class="home-series-top card-list">

            <?php foreach ($topLongestSeries as $index => $serie): ?>
                <a
                    class="card-link"
                    href="<?= $basePath; ?>manga/serie/<?= rawurlencode($serie->slug) ?>">

                    <article class="card card-bottom">
                        <p class="home-series-rank">
                            #<?= $index + 1 ?>
                        </p>

                        <div class="home-bottom-image-box">
                            <img
                                class="home-bottom-image card-image"
                                src="<?= $basePath; ?>public/images/mangas/thumbnail/<?= htmlspecialchars($serie->thumbnail . '.' . $serie->extension) ?>"
                                alt="<?= htmlspecialchars($serie->livre) ?>">
                        </div>

                        <p class="home-bottom-title">
                            <?= htmlspecialchars($serie->livre) ?>
                        </p>

                        <p class="home-bottom-subtitle">
                            <?= (int) $serie->total ?> tomes
                        </p>
                    </article>
                </a>
            <?php endforeach; ?>

        </section>
    <?php endif; ?>

    <?php if (!empty($bestRatedMangas)): ?>
        <h2 class="home-section-title">
            ⭐ Mangas notés 10/10
        </h2>

        <section class="home-best-list card-list">

            <?php foreach ($bestRatedMangas as $manga): ?>
                <a
                    class="card-link"
                    href="<?= $basePath; ?>manga/<?= rawurlencode($manga->slug) ?>/<?= (int) $manga->numero ?>">

                    <article class="card card-bottom">
                        <span class="home-note-badge">
                            ⭐ <?= (int) $manga->note ?>/10
                        </span>

                        <div class="home-bottom-image-box">
                            <img
                                class="home-bottom-image card-image"
                                src="<?= $basePath; ?>public/images/mangas/thumbnail/<?= htmlspecialchars($manga->thumbnail . '.' . $manga->extension) ?>"
                                alt="<?= htmlspecialchars($manga->livre) ?>">
                        </div>

                        <p class="home-bottom-title">
                            <?= htmlspecialchars($manga->livre) ?>
                        </p>

                        <p class="home-bottom-subtitle">
                            Tome <?= str_pad((string) $manga->numero, 2, '0', STR_PAD_LEFT) ?>
                        </p>
                    </article>
                </a>
            <?php endforeach; ?>

        </section>
    <?php endif; ?>

</section>