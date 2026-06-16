<?php

declare(strict_types=1);

use App\DTO\Chinois\Responses\ChinoisGrammaireData;

/** @var list<ChinoisGrammaireData> $grammaires */

$level =
    (string) (
        $level
        ?? '1'
    );

$baseUri =
    rtrim(
        $baseUri
        ?? '',
        '/',
    ) . '/';

$sections = [];

foreach ($grammaires as $grammaire)
{
    $section = $grammaire->section;
    $categorie = $grammaire->categorie;

    $sections[$section][$categorie][] =
        $grammaire;
}

$sectionIds = [];

foreach (array_keys($sections) as $section)
{
    $sectionId =
        transliterator_transliterate(
            'Any-Latin; Latin-ASCII',
            $section,
        );

    if ($sectionId === false)
    {
        $sectionId = $section;
    }

    $sectionId =
        mb_strtolower(
            $sectionId,
        );

    $sectionId =
        preg_replace(
            '/[^a-z0-9]+/',
            '-',
            $sectionId,
        ) ?? '';

    $sectionIds[$section] =
        trim(
            $sectionId,
            '-',
        );
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

$description =
    $descriptions[$level]
    ?? $descriptions['1'];

$sourceUrl =
    $sources[$level]
    ?? $sources['1'];

$sourceDescription =
    $sourceDescriptions[$level]
    ?? $sourceDescriptions['1'];

?>

<section class="layout-container dashboard-page">

    <section class="grammar-hero transition-title">

        <div class="grammar-hero-main">

            <h1 class="grammar-hero-title">
                📘 HSK<?= e($level) ?>
            </h1>

            <p class="grammar-hero-description">
                <?= e($description) ?>
            </p>

        </div>

        <div class="grammar-hero-source">

            <div class="grammar-source-content">

                <span class="grammar-source-label">
                    Source
                </span>

                <h2 class="grammar-source-title">
                    Chine Informations — HSK<?= e($level) ?>
                </h2>

                <p class="grammar-source-description">
                    <?= e($sourceDescription) ?>
                </p>

            </div>

            <a
                class="grammar-source-link"
                href="<?= e($sourceUrl) ?>"
                target="_blank"
                rel="noopener noreferrer"
            >
                Ouvrir
            </a>

        </div>

    </section>

    <section class="grammar-summary">

        <h2 class="grammar-summary-title">
            Sommaire
        </h2>

        <nav class="grammar-summary-links">

            <?php foreach ($sections as $section => $categories): ?>

            <a
                href="#<?= e($sectionIds[$section]) ?>"
                class="grammar-summary-link"
            >
                <?= e($section) ?>
            </a>

            <?php endforeach; ?>

        </nav>

    </section>

    <?php foreach ($sections as $section => $categories): ?>

        <section class="grammar-main-section">

            <h2
                id="<?= e($sectionIds[$section]) ?>"
                class="grammar-section-title"
            >

                <span class="grammar-section-bar"></span>

                <?= e($section) ?>

            </h2>

            <?php foreach ($categories as $categorie => $items): ?>

                <section class="grammar-category">

                    <h3 class="grammar-category-title">

                        <span class="grammar-category-bar"></span>

                        <?= e($categorie) ?>

                    </h3>

                    <section class="grammar-list">

                        <?php foreach ($items as $grammaire): ?>

                            <?php

                            $hasExplication =
                                $grammaire->explication !== null
                                && trim($grammaire->explication) !== '';

                            $hasAbreviation =
                                $grammaire->abreviation !== null
                                && trim($grammaire->abreviation) !== '';

                            $isMaitrise = $grammaire->maitrise;

                            ?>

                            <article
                                class="
                                    grammar-item
                                    transition-card
                                "
                            >

                                <button
                                    class="grammar-delete grammaire-delete"
                                    type="button"
                                    data-id="<?= $grammaire->id ?>"
                                    data-url="<?= e($baseUri) ?>chinois/ajax/delete-grammaire"
                                    aria-label="Supprimer la règle"
                                    title="Supprimer la règle"
                                >
                                    ✕
                                </button>

                                <a
                                    class="grammar-edit"
                                    href="<?= e($baseUri) ?>chinois/grammaire/<?= strtolower($grammaire->niveau) ?>/modifier/<?= $grammaire->id ?>"
                                    aria-label="Modifier la règle"
                                    title="Modifier la règle"
                                >

                                    <svg
                                        class="grammar-edit-icon"
                                        viewBox="0 0 24 24"
                                        aria-hidden="true"
                                    >

                                        <path
                                            d="M4 20H8L18.5 9.5C19.1 8.9 19.1 7.9 18.5 7.3L16.7 5.5C16.1 4.9 15.1 4.9 14.5 5.5L4 16V20Z"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        />

                                    </svg>

                                </a>

                                <h4 class="grammar-topic">
                                    <?= e($grammaire->titre) ?>
                                </h4>

                                <div class="grammar-structure">
                                    <?= e($grammaire->structure) ?>
                                </div>

                                <?php if ($hasAbreviation): ?>

                                    <div class="grammar-abbreviation">

                                        <span class="grammar-abbreviation-label">
                                            Abréviation courante :
                                        </span>

                                        <span class="grammar-abbreviation-value">
                                            <?= e($grammaire->abreviation) ?>
                                        </span>

                                    </div>

                                <?php endif; ?>

                                <div class="grammar-example">
                                    <?= e($grammaire->phrase) ?>
                                </div>

                                <div class="grammar-pinyin">
                                    <?= e($grammaire->pinyin) ?>
                                </div>

                                <div class="grammar-translation">
                                    <?= e($grammaire->traduction) ?>
                                </div>

                                <?php if ($hasExplication): ?>

                                    <div class="grammar-explanation">
                                        <?= e($grammaire->explication) ?>
                                    </div>

                                <?php endif; ?>

                                <button
                                    class="
                                        grammar-mastered
                                        grammar-ajax
                                        <?= $isMaitrise
                                            ? 'active'
                                            : ''
                            ?>
                                    "
                                    data-id="<?= $grammaire->id ?>"
                                    data-url="<?= e($baseUri) ?>chinois/ajax/toggle-grammaire-maitrise"
                                    data-maitrise="<?= $isMaitrise
                            ? '1'
                            : '0'
                            ?>"
                                    type="button"
                                    aria-pressed="<?= $isMaitrise
                                ? 'true'
                                : 'false'
                            ?>"
                                    aria-label="<?= $isMaitrise
                                ? 'Retirer la maîtrise'
                                : 'Marquer comme maîtrisé'
                            ?>"
                                    title="<?= $isMaitrise
                                ? 'Retirer la maîtrise'
                                : 'Marquer comme maîtrisé'
                            ?>"
                                >

                                    <svg
                                        class="grammar-mastered-icon"
                                        viewBox="0 0 24 24"
                                        aria-hidden="true"
                                    >

                                        <path
                                            d="M20 6L9 17L4 12"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="3"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        />

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