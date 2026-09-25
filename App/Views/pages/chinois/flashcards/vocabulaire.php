<?php

declare(strict_types=1);

use App\DTO\Chinois\Responses\ChinoisVocabulaireData;
use App\DTO\Common\Responses\ViewData;

/** @var ViewData $view */
/** @var list<ChinoisVocabulaireData> $vocabulaires */

?>

<section class="layout-container dashboard-page">

    <section
        class="chinois-vocab-panel"
        data-flashcards='<?= e(json_encode(
            $vocabulaires,
            JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
        )) ?>'
        data-base-uri="<?= e($view->baseUri) ?>"
    >

        <section class="chinois-vocab-list chinois-vocab-list--flashcard u-grid u-justify-center">

            <?php if ($vocabulaires === []): ?>

                <div class="chinois-vocab-empty u-text-center">
                    Aucun vocabulaire à réviser.
                </div>

            <?php else: ?>

                <?php $card = $vocabulaires[0]; ?>

                <article
                    class="
                        chinois-vocab-card
                        transition-card
                    "
                >

                    <div class="flashcard-navigation u-row-center">

                        <button
                            type="button"
                            id="flashcard-previous"
                            class="flashcard-nav-button u-row-center u-pointer"
                        >
                            ←
                        </button>

                        <span id="flashcard-counter">
                            Carte 1 / <?= count($vocabulaires) ?>
                        </span>

                        <button
                            type="button"
                            id="flashcard-next"
                            class="flashcard-nav-button u-row-center u-pointer"
                        >
                            →
                        </button>

                    </div>

                    <button
                        id="flashcard-delete"
                        class="grammar-delete vocabulaire-delete u-row-center u-absolute u-pointer u-bold"
                        type="button"
                        data-id="<?= $card->id ?>"
                        data-url="<?= e($view->baseUri) ?>chinois/ajax/delete-vocabulaire"
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
                        class="chinois-vocab-pinyin u-bold"
                    >
                        <?= e($card->pinyin) ?>
                    </div>

                    <div
                        id="flashcard-traduction"
                        class="chinois-vocab-translation u-bold"
                    >
                        <?= e($card->traduction) ?>
                    </div>

                    <div
                        id="flashcard-exemple"
                        class="chinois-vocab-example u-border-box"
                        <?= $card->hasExemple ? '' : 'hidden' ?>
                    >
                        <?= nl2br(e($card->exemple)) ?>
                    </div>

                    <div class="chinois-vocab-actions u-row u-absolute">

                        <a
                            id="flashcard-edit"
                            class="grammar-edit u-row-center u-absolute u-pointer"
                            href="<?= e($view->baseUri) ?>chinois/vocabulaire/<?= e($card->langue) ?>/modifier/<?= $card->id ?>"
                        >

                            <svg
                                class="grammar-edit-icon u-no-events"
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
                                <?= $card->masteredClass ?>
                             u-row-center u-absolute u-pointer"
                            type="button"
                            data-id="<?= $card->id ?>"
                            data-url="<?= e($view->baseUri) ?>chinois/ajax/toggle-vocabulaire-maitrise"
                            data-maitrise="<?= $card->masteredValue ?>"
                            aria-pressed="<?= $card->masteredPressed ?>"
                        >

                            <svg
                                class="grammar-mastered-icon u-no-events"
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