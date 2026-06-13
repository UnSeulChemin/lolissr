<?php

declare(strict_types=1);

/** @var list<App\Models\ChinoisVocabulaire> $vocabulaires */

$baseUri =
    rtrim(
        (string) ($baseUri ?? ''),
        '/',
    ) . '/';

?>

<section class="layout-container dashboard-page">

    <section
        class="chinois-vocab-panel"
        data-flashcards='<?= e(json_encode(
            $vocabulaires,
            JSON_UNESCAPED_UNICODE
            | JSON_THROW_ON_ERROR,
        )) ?>'
        data-base-uri="<?= e($baseUri) ?>"
    >

        <section class="chinois-vocab-list chinois-vocab-list--flashcard">

            <?php if ($vocabulaires === []) : ?>

                <div class="chinois-vocab-empty">

                    Aucun vocabulaire à réviser.

                </div>

            <?php else : ?>

                <?php $card = $vocabulaires[0]; ?>

                <article
                    class="
                        chinois-vocab-card
                        transition-card
                    "
                >

                    <div class="flashcard-navigation">

                        <button
                            type="button"
                            id="flashcard-previous"
                            class="flashcard-nav-button"
                        >
                            ←
                        </button>

                        <span id="flashcard-counter">
                            Carte 1 / <?= count($vocabulaires) ?>
                        </span>

                        <button
                            type="button"
                            id="flashcard-next"
                            class="flashcard-nav-button"
                        >
                            →
                        </button>

                    </div>

                    <button
                        id="flashcard-delete"
                        class="grammar-delete vocabulaire-delete"
                        type="button"
                        data-id="<?= (int) $card->id ?>"
                        data-url="<?= e($baseUri) ?>chinois/ajax/delete-vocabulaire"
                    >
                        ✕
                    </button>

                    <h3
                        id="flashcard-mot"
                        class="chinois-vocab-word"
                    >
                        <?= e($card->mot) ?>
                    </h3>

                    <div
                        id="flashcard-pinyin"
                        class="chinois-vocab-pinyin"
                    >
                        <?= e($card->pinyin) ?>
                    </div>

                    <div
                        id="flashcard-traduction"
                        class="chinois-vocab-translation"
                    >
                        <?= e($card->traduction) ?>
                    </div>

                    <div
                        id="flashcard-exemple"
                        class="chinois-vocab-example"
                    >
                        <?= nl2br(
                            e($card->exemple),
                        ) ?>
                    </div>

                    <div class="chinois-vocab-actions">

                        <a
                            id="flashcard-edit"
                            class="grammar-edit"
                            href="<?= e($baseUri) ?>chinois/vocabulaire/modifier/<?= $card->id ?>?return_to=chinois/flashcards/vocabulaire"
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
                            data-id="<?= (int) $card->id ?>"
                            data-url="<?= e($baseUri) ?>chinois/ajax/toggle-vocabulaire-maitrise"
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

                    </div>

                </article>

            <?php endif; ?>

        </section>

    </section>

</section>