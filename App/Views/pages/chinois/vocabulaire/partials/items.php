<?php

declare(strict_types=1);

use App\DTO\Chinois\Responses\ChinoisVocabulaireData;
use App\DTO\Common\Responses\ViewData;

/** @var ViewData $view */
/** @var list<ChinoisVocabulaireData> $vocabulaires */

?>

<section class="layout-container dashboard-page">

    <section class="chinois-vocab-panel">

        <section class="chinois-vocab-list u-grid">

            <?php foreach ($vocabulaires as $vocabulaire): ?>

                <article
                    class="
                        chinois-vocab-card
                        transition-card
                    "
                >

                    <button
                        class="grammar-delete vocabulaire-delete u-row-center u-absolute u-pointer u-bold"
                        type="button"
                        data-id="<?= $vocabulaire->id ?>"
                        data-url="<?= e($view->baseUri) ?>chinois/ajax/delete-vocabulaire"
                    >
                        ✕
                    </button>

                    <h3
                        class="chinois-vocab-word"
                        data-copy="<?= e($vocabulaire->mot) ?>"
                        title="Cliquer pour copier"
                    >
                        <?= e($vocabulaire->mot) ?>
                    </h3>

                    <div class="chinois-vocab-pinyin u-bold">
                        <?= e($vocabulaire->pinyin) ?>
                    </div>

                    <div class="chinois-vocab-type u-row-center u-absolute">
                        <?= e($vocabulaire->type) ?>
                    </div>

                    <div class="chinois-vocab-translation u-bold">
                        <?= e($vocabulaire->traduction) ?>
                    </div>

                    <?php if ($vocabulaire->hasExemple): ?>

                        <div
                            class="chinois-vocab-example u-border-box"
                            data-copy="<?= e($vocabulaire->exemple) ?>"
                            title="Cliquer pour copier"
                        >
                            <?= nl2br(e($vocabulaire->exemple)) ?>
                        </div>

                    <?php endif; ?>

                    <div class="chinois-vocab-actions u-row u-absolute">

                        <a
                            class="grammar-edit u-row-center u-absolute u-pointer"
                            href="<?= e($view->baseUri) ?>chinois/vocabulaire/<?= e($vocabulaire->langue) ?>/modifier/<?= $vocabulaire->id ?>"
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
                            class="
                                grammar-mastered
                                vocabulary-ajax
                                <?= $vocabulaire->masteredClass ?>
                             u-row-center u-absolute u-pointer"
                            type="button"
                            data-id="<?= $vocabulaire->id ?>"
                            data-url="<?= e($view->baseUri) ?>chinois/ajax/toggle-vocabulaire-maitrise"
                            data-maitrise="<?= $vocabulaire->masteredValue ?>"
                            aria-pressed="<?= $vocabulaire->masteredPressed ?>"
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

            <?php endforeach; ?>

        </section>

    </section>

</section>