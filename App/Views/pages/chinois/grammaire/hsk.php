<?php

declare(strict_types=1);

use App\DTO\Chinois\Responses\ChinoisHskData;
use App\DTO\Common\Responses\ViewData;

/** @var ChinoisHskData $hsk */
/** @var ViewData $view */

?>

<section class="layout-container dashboard-page">

    <section class="grammar-hero transition-title">

        <div class="grammar-hero-main">

            <h1 class="grammar-hero-title">
                📘 HSK<?= e($hsk->level) ?>
            </h1>

            <p class="grammar-hero-description">
                <?= e($hsk->description) ?>
            </p>

        </div>

        <div class="grammar-hero-source">

            <div class="grammar-source-content">

                <span class="grammar-source-label">
                    Source
                </span>

                <h2 class="grammar-source-title">
                    Chine Informations — HSK<?= e($hsk->level) ?>
                </h2>

                <p class="grammar-source-description">
                    <?= e($hsk->sourceDescription) ?>
                </p>

            </div>

            <a
                class="grammar-source-link"
                href="<?= e($hsk->sourceUrl) ?>"
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

            <?php foreach ($hsk->sections as $section): ?>

                <a
                    href="#<?= e($section->id) ?>"
                    class="grammar-summary-link"
                >
                    <?= e($section->title) ?>
                </a>

            <?php endforeach; ?>

        </nav>

    </section>

    <?php foreach ($hsk->sections as $section): ?>

    <section class="grammar-main-section">

        <h2
            id="<?= e($section->id) ?>"
            class="grammar-section-title"
        >

            <span class="grammar-section-bar"></span>

            <?= e($section->title) ?>

        </h2>

        <?php foreach ($section->categories as $categorie): ?>

            <section class="grammar-category">

                <h3 class="grammar-category-title">

                    <span class="grammar-category-bar"></span>

                    <?= e($categorie->title) ?>

                </h3>

                <section class="grammar-list">

                    <?php foreach ($categorie->grammaires as $grammaire): ?>

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
                                data-url="<?= e($view->baseUri) ?>chinois/ajax/delete-grammaire"
                                aria-label="Supprimer la règle"
                                title="Supprimer la règle"
                            >
                                ✕
                            </button>

                            <a
                                class="grammar-edit"
                                href="<?= e($view->baseUri) ?>chinois/grammaire/<?= strtolower($grammaire->niveau) ?>/modifier/<?= $grammaire->id ?>"
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

                            <?php if ($grammaire->hasAbreviation): ?>

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

                            <?php if ($grammaire->hasExplication): ?>

                                <div class="grammar-explanation">
                                    <?= e($grammaire->explication) ?>
                                </div>

                            <?php endif; ?>

                            <button
                                class="
                                    grammar-mastered
                                    grammar-ajax
                                    <?= $grammaire->masteredClass ?>
                                "
                                type="button"
                                data-id="<?= $grammaire->id ?>"
                                data-url="<?= e($view->baseUri) ?>chinois/ajax/toggle-grammaire-maitrise"
                                data-maitrise="<?= $grammaire->masteredValue ?>"
                                aria-pressed="<?= $grammaire->masteredPressed ?>"
                                aria-label="<?= $grammaire->masteredLabel ?>"
                                title="<?= $grammaire->masteredLabel ?>"
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