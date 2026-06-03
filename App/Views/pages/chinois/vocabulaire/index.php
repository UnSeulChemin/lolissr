<?php

declare(strict_types=1);

/** @var array<int, object> $vocabulaires */
/** @var string $langue */

$baseUri =
    rtrim(
        (string) ($baseUri ?? ''),
        '/',
    ) . '/';

?>

<section class="layout-container dashboard-page">

    <section
        class="
            chinois-vocab-panel
        "
    >

        <section class="chinois-vocab-list">

            <?php if ($vocabulaires === []): ?>

                <div class="chinois-vocab-empty">
                    Aucun vocabulaire enregistré.
                </div>

            <?php else: ?>

                <?php foreach ($vocabulaires as $vocabulaire): ?>

                    <?php

                    $isMaitrise =
                        (int) (
                            $vocabulaire->maitrise
                            ?? 0
                        ) === 1;

                    ?>

                    <article
                        class="
                            chinois-vocab-card
                            transition-card
                        "
                    >

                        <button
                            class="grammar-delete"
                            type="button"
                            data-id="<?= (int) $vocabulaire->id ?>"
                            data-url="<?= e($baseUri) ?>chinois/ajax/delete-vocabulaire"
                        >
                            ✕
                        </button>

                        <h3 class="chinois-vocab-word">
                            <?= e((string) $vocabulaire->mot) ?>
                        </h3>

                        <div class="chinois-vocab-pinyin">
                            <?= e((string) $vocabulaire->pinyin) ?>
                        </div>

                        <div class="chinois-vocab-type">
                            <?= e((string) $vocabulaire->type) ?>
                        </div>

                        <div class="chinois-vocab-translation">
                            <?= e((string) $vocabulaire->traduction) ?>
                        </div>

                        <?php if (
                            trim(
                                (string) $vocabulaire->exemple,
                            ) !== ''
                        ): ?>

                            <div class="chinois-vocab-example">
                                <?= e((string) $vocabulaire->exemple) ?>
                            </div>

                        <?php endif; ?>

                        <div class="chinois-vocab-actions">

                            <a
                                class="grammar-edit"
                                href="<?= e($baseUri) ?>chinois/vocabulaire/modifier/<?= (int) $vocabulaire->id ?>"
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
                                    <?= $isMaitrise
                                        ? 'active'
                                        : '' ?>
                                "
                                type="button"
                                data-id="<?= (int) $vocabulaire->id ?>"
                                data-url="<?= e($baseUri) ?>chinois/ajax/toggle-vocabulaire-maitrise"
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

                <?php endforeach; ?>

            <?php endif; ?>

        </section>

    </section>

</section>