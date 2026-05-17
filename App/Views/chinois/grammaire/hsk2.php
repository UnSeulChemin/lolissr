<?php

/**
 * @var \App\DTO\Chinois\ChinoisGrammaireDTO[] $grammaires
 */

$grammaires = $grammaires ?? [];

$sections = [];

foreach ($grammaires as $grammaire)
{
    $sections[$grammaire->section][$grammaire->categorie][] = $grammaire;
}

?>

<section class="layout-container dashboard-page">

    <section class="grammar-hero animate-fade-up">

        <div class="grammar-hero-main">

            <h1 class="grammar-hero-title">
                📘 HSK2
            </h1>

            <p class="grammar-hero-description">
                Structures courantes, phrases du quotidien et grammaire HSK2.
            </p>

        </div>

        <div class="grammar-hero-source">

            <div class="grammar-source-content">

                <span class="grammar-source-label">
                    Source
                </span>

                <h2 class="grammar-source-title">
                    Chine Informations — HSK2
                </h2>

                <p class="grammar-source-description">
                    Références, structures et exemples de grammaire chinoise pour débutants intermédiaires.
                </p>

            </div>

            <a
                class="grammar-source-link"
                href="https://chine.in/mandarin/grammaire/RGLA2"
                target="_blank"
                rel="noopener noreferrer">
                Ouvrir
            </a>

        </div>

    </section>

    <?php foreach ($sections as $section => $categories): ?>

        <section class="grammar-main-section">

            <h2 class="grammar-section-title">

                <span class="grammar-section-bar"></span>

                <?= htmlspecialchars($section) ?>

            </h2>

            <?php foreach ($categories as $categorie => $items): ?>

                <section class="grammar-category">

                    <h3 class="grammar-category-title">

                        <span class="grammar-category-bar"></span>

                        <?= htmlspecialchars($categorie) ?>

                    </h3>

                    <section class="grammar-list">

                        <?php foreach ($items as $grammaire): ?>

                            <article class="grammar-item">

                                <h4 class="grammar-topic">
                                    <?= htmlspecialchars($grammaire->titre) ?>
                                </h4>

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

                                <?php if (!empty($grammaire->explication)): ?>

                                    <div class="grammar-explanation">
                                        <?= htmlspecialchars($grammaire->explication) ?>
                                    </div>

                                <?php endif; ?>

                            </article>

                        <?php endforeach; ?>

                    </section>

                </section>

            <?php endforeach; ?>

        </section>

    <?php endforeach; ?>

</section>