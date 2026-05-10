<?php

/**
 * @var \App\DTO\Chinois\ChinoisGrammaireDTO[] $grammaires
 */

$grammaires = $grammaires ?? [];

?>

<section class="layout-container dashboard-page">

    <section class="grammar-hero animate-fade-up">

        <div class="grammar-hero-main">

            <h1 class="grammar-hero-title">
                📘 HSK1
            </h1>

            <p class="grammar-hero-description">
                Points de grammaire, structures de base et notes pour le niveau HSK1.
            </p>

        </div>

        <div class="grammar-hero-source">

            <div>

                <span class="grammar-source-label">
                    Source
                </span>

                <h2 class="grammar-source-title">
                    Chine Informations — HSK1
                </h2>

                <p class="grammar-source-description">
                    Liste des points de grammaire HSK1 avec exemples et explications.
                </p>

            </div>

            <a
                class="grammar-source-link"
                href="https://chine.in/mandarin/grammaire/RGLA1"
                target="_blank"
                rel="noopener noreferrer">
                Ouvrir
            </a>

        </div>

    </section>

    <section class="grammar-list">

        <?php foreach ($grammaires as $grammaire): ?>

            <article class="grammar-item">

                <div class="grammar-topic">
                    <?= htmlspecialchars($grammaire->titre) ?>
                </div>

                <div class="grammar-structure">
                    <?= htmlspecialchars($grammaire->structure) ?>
                </div>

                <div class="grammar-example">
                    <?= htmlspecialchars($grammaire->phrase) ?>
                </div>

                <div class="grammar-pinyin">
                    <?= htmlspecialchars($grammaire->pinyin) ?>
                </div>

                <div class="grammar-translation">
                    <?= htmlspecialchars($grammaire->traduction) ?>
                </div>

                <div class="grammar-explanation">
                    <?= htmlspecialchars($grammaire->explication) ?>
                </div>

            </article>

        <?php endforeach; ?>

    </section>

</section>