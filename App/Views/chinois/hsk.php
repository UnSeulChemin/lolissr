<?php

/**
 * @var \App\DTO\Chinois\ChinoisGrammaireDTO[] $grammaires
 * @var string $level
 */

$grammaires = $grammaires ?? [];
$level = $level ?? '1';

$sections = [];

foreach ($grammaires as $grammaire)
{
    $sections[$grammaire->section][$grammaire->categorie][] = $grammaire;
}

$descriptions = [
    '1' => 'Structures courantes, phrases du quotidien et grammaire HSK1.',
    '2' => 'Structures courantes, phrases du quotidien et grammaire HSK2.',
    '3' => 'Structures intermédiaires, phrases naturelles et grammaire HSK3.',
    '4' => 'Structures avancées, nuances et grammaire HSK4.',
];

$sources = [
    '1' => 'https://chine.in/mandarin/grammaire/RGLA1',
    '2' => 'https://chine.in/mandarin/grammaire/RGLA2',
    '3' => 'https://chine.in/mandarin/grammaire/RGLB1',
    '4' => 'https://chine.in/mandarin/grammaire/RGLB2',
];

$sourceDescriptions = [
    '1' => 'Références, structures et exemples de grammaire chinoise pour débutants.',
    '2' => 'Références, structures et exemples de grammaire chinoise pour débutants intermédiaires.',
    '3' => 'Références, structures et exemples de grammaire chinoise intermédiaire.',
    '4' => 'Références, structures et exemples de grammaire chinoise avancée.',
];

?>

<section class="layout-container dashboard-page">

    <section class="grammar-hero animate-fade-up">

        <div class="grammar-hero-main">

            <h1 class="grammar-hero-title">
                📘 HSK<?= htmlspecialchars($level) ?>
            </h1>

            <p class="grammar-hero-description">
                <?= htmlspecialchars($descriptions[$level]) ?>
            </p>

        </div>

        <div class="grammar-hero-source">

            <div class="grammar-source-content">

                <span class="grammar-source-label">
                    Source
                </span>

                <h2 class="grammar-source-title">
                    Chine Informations — HSK<?= htmlspecialchars($level) ?>
                </h2>

                <p class="grammar-source-description">
                    <?= htmlspecialchars($sourceDescriptions[$level]) ?>
                </p>

            </div>

            <a
                class="grammar-source-link"
                href="<?= htmlspecialchars($sources[$level]) ?>"
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

                                <button
                                    class="grammar-mastered <?= !empty($grammaire->maitrise) ? 'active' : '' ?>"
                                    data-id="<?= $grammaire->id ?>"
                                    data-url="<?= $basePath; ?>/chinois/ajax/toggle-grammaire-maitrise"
                                    data-maitrise="<?= !empty($grammaire->maitrise) ? '1' : '0' ?>"
                                    type="button"
                                    aria-label="Marquer comme maîtrisé">

                                    <svg
                                        class="grammar-mastered-icon"
                                        viewBox="0 0 24 24"
                                        aria-hidden="true">

                                        <path
                                            d="M20 6L9 17L4 12"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="3"
                                            stroke-linecap="round"
                                            stroke-linejoin="round" />

                                    </svg>

                                </button>

                            </article>

                        <?php endforeach; ?>

                    </section>

                </section>

            <?php endforeach; ?>

        </section>

    <?php endforeach; ?>

</section>