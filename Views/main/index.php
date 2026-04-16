<section class="layout-container">

    <section class="home-grid home-grid-top card-grid-3 animate-fade-up-stagger">

        <?php if (!empty($longestSeries)): ?>
            <a
                class="card card-link card-link-wide card-wide"
                href="<?= $basePath; ?>manga/serie/<?= rawurlencode($longestSeries->slug) ?>">

                <h2 class="home-card-title">
                    📚 Série la plus longue
                </h2>

                <div class="home-longest-content">
                    <div class="card-image-box-portrait home-feature-image-box">
                        <img
                            class="card-image-portrait"
                            src="<?= $basePath; ?>public/images/mangas/thumbnail/<?= htmlspecialchars($longestSeries->thumbnail . '.' . $longestSeries->extension) ?>"
                            alt="<?= htmlspecialchars($longestSeries->livre) ?>">
                    </div>

                    <div class="home-longest-info">
                        <p class="home-longest-name">
                            <?= htmlspecialchars($longestSeries->livre) ?>
                        </p>

                        <p class="home-longest-count">
                            <?= (int) $longestSeries->total ?> tomes
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

        <?php if (!empty($lastTome)): ?>
            <a
                class="card card-link card-medium"
                href="<?= $basePath; ?>manga/<?= rawurlencode($lastTome->slug) ?>/<?= (int) $lastTome->numero ?>">

                <h2 class="home-card-title">
                    🆕 Dernier tome ajouté
                </h2>

                <div class="home-feature-content">
                    <div class="card-image-box-portrait home-feature-image-box">
                        <img
                            class="card-image-portrait"
                            src="<?= $basePath; ?>public/images/mangas/thumbnail/<?= htmlspecialchars($lastTome->thumbnail . '.' . $lastTome->extension) ?>"
                            alt="<?= htmlspecialchars($lastTome->livre) ?>">
                    </div>

                    <div class="home-feature-info">
                        <p class="home-feature-title">
                            <?= htmlspecialchars($lastTome->livre) ?>
                        </p>

                        <p class="home-feature-meta">
                            Tome <?= str_pad((string) $lastTome->numero, 2, '0', STR_PAD_LEFT) ?>
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
                ⭐ Note moyenne globale
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

        <section class="home-ranking-list card-list animate-fade-up-stagger">

            <?php foreach ($topLongestSeries as $index => $serie): ?>
                <a
                    class="card card-link card-bottom"
                    href="<?= $basePath; ?>manga/serie/<?= rawurlencode($serie->slug) ?>">

                    <p class="home-series-rank">
                        #<?= $index + 1 ?>
                    </p>

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

    <h2 class="home-section-title">
        ⚠️ Mangas à surveiller
    </h2>

    <?php
    $lowGlobal = $lowRatedMangas[0] ?? null;
    $lowJacquette = $lowJacquetteMangas[0] ?? null;
    $lowLivre = $lowLivreStateMangas[0] ?? null;
    ?>

    <section class="home-grid home-grid-watch card-grid-3 animate-fade-up-stagger">

        <?php if ($lowGlobal): ?>
            <a
                class="card card-link card-medium"
                href="<?= $basePath; ?>manga/<?= rawurlencode($lowGlobal->slug) ?>/<?= (int) $lowGlobal->numero ?>">

                <h2 class="home-card-title">
                    📉 À remplacer
                </h2>

                <div class="home-feature-content">
                    <div class="card-image-box-portrait home-feature-image-box">
                        <img
                            class="card-image-portrait"
                            src="<?= $basePath; ?>public/images/mangas/thumbnail/<?= htmlspecialchars($lowGlobal->thumbnail . '.' . $lowGlobal->extension) ?>"
                            alt="<?= htmlspecialchars($lowGlobal->livre) ?>">
                    </div>

                    <div class="home-feature-info">
                        <p class="home-feature-title">
                            <?= htmlspecialchars($lowGlobal->livre) ?>
                        </p>

                        <p class="home-feature-meta">
                            Tome <?= str_pad((string) $lowGlobal->numero, 2, '0', STR_PAD_LEFT) ?>
                        </p>

                        <p class="home-feature-meta">
                            ⭐ <?= (int) $lowGlobal->note ?>/10
                        </p>
                    </div>
                </div>
            </a>
        <?php else: ?>
            <article class="card card-medium">
                <h2 class="home-card-title">
                    📉 À remplacer
                </h2>

                <p class="home-empty">
                    Aucun manga sous 8/10
                </p>
            </article>
        <?php endif; ?>

        <?php if ($lowJacquette): ?>
            <a
                class="card card-link card-medium"
                href="<?= $basePath; ?>manga/<?= rawurlencode($lowJacquette->slug) ?>/<?= (int) $lowJacquette->numero ?>">

                <h2 class="home-card-title">
                    🧥 Jacquette faible
                </h2>

                <div class="home-feature-content">
                    <div class="card-image-box-portrait home-feature-image-box">
                        <img
                            class="card-image-portrait"
                            src="<?= $basePath; ?>public/images/mangas/thumbnail/<?= htmlspecialchars($lowJacquette->thumbnail . '.' . $lowJacquette->extension) ?>"
                            alt="<?= htmlspecialchars($lowJacquette->livre) ?>">
                    </div>

                    <div class="home-feature-info">
                        <p class="home-feature-title">
                            <?= htmlspecialchars($lowJacquette->livre) ?>
                        </p>

                        <p class="home-feature-meta">
                            Tome <?= str_pad((string) $lowJacquette->numero, 2, '0', STR_PAD_LEFT) ?>
                        </p>

                        <p class="home-feature-meta">
                            ⭐ <?= (int) $lowJacquette->jacquette ?>/5
                        </p>
                    </div>
                </div>
            </a>
        <?php else: ?>
            <article class="card card-medium">
                <h2 class="home-card-title">
                    🧥 Jacquette faible
                </h2>

                <p class="home-empty">
                    Aucune jacquette sous 4/5
                </p>
            </article>
        <?php endif; ?>

        <?php if ($lowLivre): ?>
            <a
                class="card card-link card-medium"
                href="<?= $basePath; ?>manga/<?= rawurlencode($lowLivre->slug) ?>/<?= (int) $lowLivre->numero ?>">

                <h2 class="home-card-title">
                    📘 Livre abîmé
                </h2>

                <div class="home-feature-content">
                    <div class="card-image-box-portrait home-feature-image-box">
                        <img
                            class="card-image-portrait"
                            src="<?= $basePath; ?>public/images/mangas/thumbnail/<?= htmlspecialchars($lowLivre->thumbnail . '.' . $lowLivre->extension) ?>"
                            alt="<?= htmlspecialchars($lowLivre->livre) ?>">
                    </div>

                    <div class="home-feature-info">
                        <p class="home-feature-title">
                            <?= htmlspecialchars($lowLivre->livre) ?>
                        </p>

                        <p class="home-feature-meta">
                            Tome <?= str_pad((string) $lowLivre->numero, 2, '0', STR_PAD_LEFT) ?>
                        </p>

                        <p class="home-feature-meta">
                            ⭐ <?= (int) $lowLivre->livre_note ?>/5
                        </p>
                    </div>
                </div>
            </a>
        <?php else: ?>
            <article class="card card-medium">
                <h2 class="home-card-title">
                    📘 Livre abîmé
                </h2>

                <p class="home-empty">
                    Aucun livre sous 4/5
                </p>
            </article>
        <?php endif; ?>

    </section>

    <h2 class="home-section-title">
        📉 Mangas les moins bien notés
    </h2>

    <?php if (!empty($lowRatedMangas)): ?>
        <section class="home-low-rated-list card-list animate-fade-up-stagger">

            <?php foreach ($lowRatedMangas as $manga): ?>
                <a
                    class="card card-link card-bottom"
                    href="<?= $basePath; ?>manga/<?= rawurlencode($manga->slug) ?>/<?= (int) $manga->numero ?>">

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
    <?php else: ?>
        <p class="home-empty">
            Aucun manga avec une note inférieure à 8.
        </p>
    <?php endif; ?>

</section>