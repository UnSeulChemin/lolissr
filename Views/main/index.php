<section class="section-content">

    <h1 class="card-banner">
        Accueil
    </h1>

    <section class="home-grid home-grid-top">

        <article class="home-card home-card-small">
            <p class="home-card-title">
                📚 Total tomes
            </p>

            <p class="home-card-value">
                <?= (int) $totalTomes ?> tomes
            </p>
        </article>

        <article class="home-card home-card-small">
            <p class="home-card-title">
                📖 Total séries
            </p>

            <p class="home-card-value">
                <?= (int) $totalSeries ?> séries
            </p>
        </article>

        <?php if (!empty($lastTome)): ?>
            <a
                class="home-card-link"
                href="<?= $basePath; ?>manga/collection/<?= rawurlencode($lastTome->slug) ?>/<?= (int) $lastTome->numero ?>">

                <article class="home-card home-card-small">

                    <p class="home-card-title">
                        🆕 Dernier tome ajouté
                    </p>

                    <img
                        class="home-card-image"
                        src="<?= $basePath; ?>public/images/mangas/thumbnail/<?= htmlspecialchars($lastTome->thumbnail . '.' . $lastTome->extension) ?>"
                        alt="<?= htmlspecialchars($lastTome->livre) ?>">

                    <p class="home-card-text">
                        <?= htmlspecialchars($lastTome->livre) ?>
                    </p>

                    <p class="home-card-subtext">
                        Tome <?= str_pad((string) $lastTome->numero, 2, '0', STR_PAD_LEFT) ?>
                    </p>

                </article>
            </a>
        <?php endif; ?>

    </section>

    <section class="home-grid home-grid-middle">

        <?php if (!empty($longestSeries)): ?>
            <a
                class="home-card-link"
                href="<?= $basePath; ?>manga/collection/<?= rawurlencode($longestSeries->slug) ?>">

                <article class="home-card home-card-wide">

                    <p class="home-card-title">
                        📚 Série la plus longue
                    </p>

                    <div class="home-longest-content">

                        <img
                            class="home-longest-image"
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
            <article class="home-card home-card-wide">
                <p class="home-card-title">
                    📚 Série la plus longue
                </p>

                <p class="home-empty">
                    Aucune donnée
                </p>
            </article>
        <?php endif; ?>

        <article class="home-card home-card-medium">
            <p class="home-card-title">
                📉 Note moyenne globale
            </p>

            <p class="home-card-value">
                <?= $averageNote !== null ? number_format((float) $averageNote, 1, ',', ' ') . '/10' : 'Aucune note' ?>
            </p>
        </article>

    </section>

    <?php if (!empty($bestRatedMangas)): ?>
        <h2 class="card-banner home-subtitle">
            ⭐ Mangas notés 10/10
        </h2>

        <section class="home-best-list">

            <?php foreach ($bestRatedMangas as $manga): ?>
                <a
                    class="home-best-link"
                    href="<?= $basePath; ?>manga/collection/<?= rawurlencode($manga->slug) ?>/<?= (int) $manga->numero ?>">

                    <article class="home-card home-best-card">

                        <span class="home-note-badge">
                            <?= (int) $manga->note ?>/10
                        </span>

                        <img
                            class="home-best-image"
                            src="<?= $basePath; ?>public/images/mangas/thumbnail/<?= htmlspecialchars($manga->thumbnail . '.' . $manga->extension) ?>"
                            alt="<?= htmlspecialchars($manga->livre) ?>">

                        <p class="home-best-title">
                            <?= htmlspecialchars($manga->livre) ?>
                        </p>

                        <p class="home-best-volume">
                            Tome <?= str_pad((string) $manga->numero, 2, '0', STR_PAD_LEFT) ?>
                        </p>

                        <p class="home-best-label">
                            Coup de cœur
                        </p>

                    </article>
                </a>
            <?php endforeach; ?>

        </section>
    <?php endif; ?>

    <?php if (!empty($topLongestSeries)): ?>
        <h2 class="card-banner home-subtitle">
            📊 Top 5 séries les plus longues
        </h2>

        <section class="home-series-top">

            <?php foreach ($topLongestSeries as $index => $serie): ?>
                <a
                    class="home-card-link"
                    href="<?= $basePath; ?>manga/collection/<?= rawurlencode($serie->slug) ?>">

                    <article class="home-card home-series-card">

                        <p class="home-series-rank">
                            #<?= $index + 1 ?>
                        </p>

                        <img
                            class="home-series-image"
                            src="<?= $basePath; ?>public/images/mangas/thumbnail/<?= htmlspecialchars($serie->thumbnail . '.' . $serie->extension) ?>"
                            alt="<?= htmlspecialchars($serie->livre) ?>">

                        <p class="home-series-name">
                            <?= htmlspecialchars($serie->livre) ?>
                        </p>

                        <p class="home-series-count">
                            <?= (int) $serie->total ?> tomes
                        </p>

                    </article>
                </a>
            <?php endforeach; ?>

        </section>
    <?php endif; ?>

</section>