<?php

declare(strict_types=1);

/** @var list<App\Models\ChinoisVocabulaire> $vocabulaires */

$baseUri =
    rtrim(
        (string) ($baseUri ?? ''),
        '/',
    ) . '/';

?>

<section class="layout-container">

    <?php if ($vocabulaires === []) : ?>

        <article class="card">

            <h2>
                Aucun vocabulaire à réviser.
            </h2>

        </article>

    <?php else : ?>

        <?php $card = $vocabulaires[0]; ?>

        <article
            class="
                card
                transition-card
            "
        >

            <header>

                <button
                    type="button"
                    id="flashcard-previous"
                >
                    ←
                </button>

                <p id="flashcard-counter">
                    Carte 1 / <?= count($vocabulaires) ?>
                </p>

                <button
                    type="button"
                    id="flashcard-next"
                >
                    →
                </button>

            </header>

            <section>

                <h2 id="flashcard-mot">
                    <?= e($card->mot) ?>
                </h2>

                <hr>

                <p id="flashcard-pinyin">
                    <?= e($card->pinyin) ?>
                </p>

                <p id="flashcard-traduction">
                    <?= e($card->traduction) ?>
                </p>

                <p id="flashcard-exemple">
                    <?= nl2br(
                        e($card->exemple),
                    ) ?>
                </p>

            </section>

            <footer>

                <a
                    id="flashcard-edit"
                    class="button"
                    href="<?= e($baseUri) ?>chinois/vocabulaire/modifier/<?= $card->id ?>"
                >
                    Modifier
                </a>

            </footer>

        </article>

    <?php endif; ?>

</section>

<script>
    window.flashcards =
        <?= json_encode(
             $vocabulaires,
                JSON_UNESCAPED_UNICODE,
        ) ?>;

    window.baseUri =
        <?= json_encode(
            $baseUri,
        ) ?>;
</script>