<?php

declare(strict_types=1);

/** @var list<App\Models\ChinoisVocabulaire> $vocabulaires */

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

                <p>
                    Carte 1 / <?= count($vocabulaires) ?>
                </p>

                <h2>
                    <?= e($card->mot) ?>
                </h2>

            </header>

            <hr>

            <p>
                <?= e($card->pinyin) ?>
            </p>

            <p>
                <?= e($card->traduction) ?>
            </p>

            <?php if ($card->exemple !== '') : ?>

                <p>
                    <?= nl2br(
                        e($card->exemple),
                    ) ?>
                </p>

            <?php endif; ?>

            <footer>

                <a
                    class="button"
                    href="<?= e($baseUri) ?>chinois/vocabulaire/modifier/<?= $card->id ?>"
                >
                    Modifier
                </a>

            </footer>

        </article>

    <?php endif; ?>

</section>