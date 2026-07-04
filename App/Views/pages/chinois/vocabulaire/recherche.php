<?php

declare(strict_types=1);

/** @var App\DTO\Chinois\Responses\ChinoisVocabulaireData $vocabulaire */

$isMaitrise =
    $vocabulaire->maitrise;

?>

<section class="layout-container dashboard-page">

    <section class="chinois-vocab-panel">

        <section class="chinois-vocab-list">

            <article
                class="
                    chinois-vocab-card
                    transition-card
                "
            >

                <button
                    class="grammar-delete vocabulaire-delete"
                    type="button"
                    data-id="<?= $vocabulaire->id ?>"
                    data-url="<?= e($view->baseUri) ?>chinois/ajax/delete-vocabulaire"
                >
                    ✕
                </button>

                <h1
                    class="chinois-vocab-word"
                    data-copy="<?= e($vocabulaire->mot) ?>"
                    title="Cliquer pour copier"
                >
                    <?= e($vocabulaire->mot) ?>
                </h1>

                <div class="chinois-vocab-pinyin">
                    <?= e($vocabulaire->pinyin) ?>
                </div>

                <div class="chinois-vocab-type">
                    <?= e($vocabulaire->type) ?>
                </div>

                <div class="chinois-vocab-translation">
                    <?= e($vocabulaire->traduction) ?>
                </div>

                <?php if (
                    trim($vocabulaire->exemple ?? '') !== ''
                ): ?>

                    <div
                        class="chinois-vocab-example"
                        data-copy="<?= e($vocabulaire->exemple ?? '') ?>"
                        title="Cliquer pour copier"
                    >
                        <?= e($vocabulaire->exemple ?? '') ?>
                    </div>

                <?php endif; ?>

                <div class="chinois-vocab-actions">

                    <a
                        class="grammar-edit"
                        href="<?= e($view->baseUri) ?>chinois/vocabulaire/<?= $vocabulaire->langue ?>/modifier/<?= $vocabulaire->id ?>"
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
                        class="
                            grammar-mastered
                            vocabulary-ajax
                            <?= $isMaitrise
                                ? 'active'
                                : '' ?>
                        "
                        type="button"
                        data-id="<?= $vocabulaire->id ?>"
                        data-url="<?= e($view->baseUri) ?>chinois/ajax/toggle-vocabulaire-maitrise"
                        data-maitrise="<?= $isMaitrise
                            ? '1'
                            : '0' ?>"
                        aria-pressed="<?= $isMaitrise
                            ? 'true'
                            : 'false' ?>"
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

        </section>

    </section>

</section>