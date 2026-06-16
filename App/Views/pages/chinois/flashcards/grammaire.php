<?php

declare(strict_types=1);

/** @var list<App\Models\ChinoisGrammaire> $grammaires */

$baseUri =
    rtrim(
        (string) ($baseUri ?? ''),
        '/',
    ) . '/';

?>

<section class="layout-container dashboard-page">

    <section
        class="grammar-main-section"
        data-flashcards='<?= e(json_encode(
            $grammaires,
            JSON_UNESCAPED_UNICODE
            | JSON_THROW_ON_ERROR,
        )) ?>'
        data-base-uri="<?= e($baseUri) ?>"
    >

        <?php if ($grammaires === []) : ?>

            <article class="grammar-item">

                <h2>
                    Aucune grammaire à réviser.
                </h2>

            </article>

        <?php else : ?>

            <?php $card = $grammaires[0]; ?>

            <article
                class="
                    grammar-item
                    transition-card
                "
            >

                <div class="flashcard-navigation">

                    <button
                        id="flashcard-previous"
                        class="flashcard-nav-button"
                        type="button"
                    >
                        ←
                    </button>

                    <span id="flashcard-counter">
                        Carte 1 / <?= count($grammaires) ?>
                    </span>

                    <button
                        id="flashcard-next"
                        class="flashcard-nav-button"
                        type="button"
                    >
                        →
                    </button>

                </div>

                <button
                    id="flashcard-delete"
                    class="grammar-delete grammaire-delete"
                    type="button"
                    data-id="<?= $card->id ?>"
                    data-url="<?= e($baseUri) ?>chinois/ajax/delete-grammaire"
                >
                    ✕
                </button>

                <h3
                    id="flashcard-titre"
                    class="grammar-topic"
                >
                    <?= e($card->titre) ?>
                </h3>

                <div
                    id="flashcard-structure"
                    class="grammar-structure"
                >
                    <?= e($card->structure) ?>
                </div>

                <div
                    id="flashcard-phrase"
                    class="grammar-example"
                >
                    <?= e($card->phrase) ?>
                </div>

                <div
                    id="flashcard-pinyin"
                    class="grammar-pinyin"
                >
                    <?= e($card->pinyin) ?>
                </div>

                <div
                    id="flashcard-traduction"
                    class="grammar-translation"
                >
                    <?= e($card->traduction) ?>
                </div>

                <div
                    id="flashcard-explication"
                    class="grammar-explanation"
                >
                    <?= e($card->explication) ?>
                </div>

                <a
                    id="flashcard-edit"
                    class="grammar-edit"
                    href="<?= e($baseUri) ?>chinois/flashcards/grammaire/modifier/<?= $card->id ?>"
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

                <button
                    id="flashcard-mastered"
                    class="
                        grammar-mastered
                    "
                    type="button"
                    data-id="<?= $card->id ?>"
                    data-url="<?= e($baseUri) ?>chinois/ajax/toggle-grammaire-maitrise"
                    data-maitrise="0"
                    aria-pressed="false"
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

        <?php endif; ?>

    </section>

</section>