<section class="section-content">

    <h1 class="card-banner">
        Accueil
    </h1>

    <section class="home-top">

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
            <article class="home-card home-card-small">

                <p class="home-card-title">
                    🆕 Dernier tome ajouté
                </p>

                <img
                    class="home-card-image"
                    alt="<?= htmlspecialchars($lastTome->livre) ?>"
                    src="<?= $basePath; ?>public/images/mangas/thumbnail/<?= htmlspecialchars($lastTome->thumbnail . '.' . $lastTome->extension) ?>">

                <p class="home-card-text">
                    <?= htmlspecialchars($lastTome->livre) ?>
                    — Tome <?= str_pad((string) $lastTome->numero, 2, '0', STR_PAD_LEFT) ?>
                </p>

            </article>
        <?php endif; ?>

    </section>

    <section class="home-middle">

        <article class="home-card home-card-wide">
            <p class="home-card-title">
                📚 Série la plus longue
            </p>

            <?php if (!empty($longestSeries)): ?>
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
            <?php else: ?>
                <p class="home-empty">
                    Aucune donnée
                </p>
            <?php endif; ?>
        </article>

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
            ⭐ Mangas avec la meilleure note (10/10)
        </h2>

        <section class="home-best-list">

            <?php foreach ($bestRatedMangas as $manga): ?>
                <article class="home-card home-best-card">

                    <img
                        class="home-card-image"
                        alt="<?= htmlspecialchars($manga->livre) ?>"
                        src="<?= $basePath; ?>public/images/mangas/thumbnail/<?= htmlspecialchars($manga->thumbnail . '.' . $manga->extension) ?>">

                    <p class="home-card-text">
                        <?= htmlspecialchars($manga->livre) ?>
                        — Tome <?= str_pad((string) $manga->numero, 2, '0', STR_PAD_LEFT) ?>
                    </p>

                    <span class="home-note-badge">
                        <?= (int) $manga->note ?>/10
                    </span>

                </article>
            <?php endforeach; ?>

        </section>

    <?php endif; ?>

    <?php if (!empty($topLongestSeries)): ?>

        <h2 class="card-banner home-subtitle">
            📊 Top 5 séries les plus longues
        </h2>

        <section class="home-series-top">

            <?php foreach ($topLongestSeries as $index => $serie): ?>
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
            <?php endforeach; ?>

        </section>

    <?php endif; ?>

</section>