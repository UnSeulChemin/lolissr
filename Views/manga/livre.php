<section class="section-content">

    <section class="card-character flex-gap-25">

        <figure class="card-character-img">

            <div class="card-character-img-inner">

                <img
                    alt="<?= htmlspecialchars($manga->livre) ?>"
                    src="<?= $basePath; ?>public/images/mangas/thumbnail/<?= htmlspecialchars($manga->thumbnail . '.' . $manga->extension) ?>">

                <?php if ($manga->note !== null): ?>
                    <?php
                    $noteClass = 'note-mid';

                    if ((int) $manga->note >= 8)
                    {
                        $noteClass = 'note-good';
                    }
                    elseif ((int) $manga->note <= 4)
                    {
                        $noteClass = 'note-low';
                    }
                    ?>
                    <span class="badge-note <?= $noteClass; ?>">
                        <?= (int) $manga->note ?>
                    </span>
                <?php endif; ?>

            </div>

        </figure>

        <article class="card-character-value">

            <p class="card-banner table-colonne">
                Livre
            </p>
            <p class="card-banner">
                <?= htmlspecialchars($manga->livre) ?>
            </p>

            <p class="card-banner table-colonne">
                Tome
            </p>
            <p class="card-banner">
                <?= str_pad((string) ((int) $manga->numero), 2, '0', STR_PAD_LEFT) ?>
            </p>

            <p class="card-banner table-colonne">
                Jacquette
            </p>
            <p class="card-banner">
                <?= $manga->jacquette !== null ? (int) $manga->jacquette . '/5' : 'Non noté' ?>
            </p>

            <p class="card-banner table-colonne">
                État du livre
            </p>
            <p class="card-banner">
                <?= $manga->livre_note !== null ? (int) $manga->livre_note . '/5' : 'Non noté' ?>
            </p>

            <p class="card-banner table-colonne">
                Note totale
            </p>
            <p class="card-banner">
                <?= $manga->note !== null ? (int) $manga->note . '/10' : 'Non calculée' ?>
            </p>

            <a class="link-edit"
               href="<?= $basePath; ?>manga/edit/<?= htmlspecialchars($manga->slug) ?>/<?= (int) $manga->numero ?>">
                Modifier
            </a>

        </article>

    </section>

    <div class="m-t-30">
        <a class="link-section"
           href="<?= $basePath; ?>manga/collection/<?= htmlspecialchars($manga->slug) ?>">
            Retour
        </a>
    </div>

</section>