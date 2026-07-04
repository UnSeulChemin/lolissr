<?php

declare(strict_types=1);

/** @var App\DTO\Chinois\Responses\ChinoisGrammaireData $grammaire */

$hasExplication =
    $grammaire->explication !== null
    && trim($grammaire->explication) !== '';

$hasAbreviation =
    $grammaire->abreviation !== null
    && trim($grammaire->abreviation) !== '';

$isMaitrise =
    $grammaire->maitrise;

?>

<section class="layout-container dashboard-page">

    <section class="grammar-list">

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
            >
                ✕
            </button>

            <a
                class="grammar-edit"
                href="<?= e($view->baseUri) ?>chinois/grammaire/<?= strtolower($grammaire->niveau) ?>/modifier/<?= $grammaire->id ?>"
            >

                <svg
                    class="grammar-edit-icon"
                    viewBox="0 0 24 24"
                >
                    <path
                        d="M4 20H8L18.5 9.5C19.1 8.9 19.1 7.9 18.5 7.3L16.7 5.5C16.1 4.9 15.1 4.9 14.5 5.5L4 16V20Z"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    />
                </svg>

            </a>

            <h1 class="grammar-topic">
                <?= e($grammaire->titre) ?>
            </h1>

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
                    <?= $isMaitrise ? 'active' : '' ?>
                "
                data-id="<?= $grammaire->id ?>"
                data-url="<?= e($view->baseUri) ?>chinois/ajax/toggle-grammaire-maitrise"
                data-maitrise="<?= $isMaitrise ? '1' : '0' ?>"
                type="button"
            >

                <svg
                    class="grammar-mastered-icon"
                    viewBox="0 0 24 24"
                >
                    <path
                        d="M20 6L9 17L4 12"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="3"
                    />
                </svg>

            </button>

        </article>

    </section>

</section>