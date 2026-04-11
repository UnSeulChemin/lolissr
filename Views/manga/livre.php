<section class="section-content">

    <section class="card-character flex-gap-25">

        <figure class="card-character-img">

            <div class="card-character-img-inner">

                <img 
                    alt="<?= htmlspecialchars($manga->livre) ?>"
                    src="<?= $basePath; ?>public/images/mangas/thumbnail/<?= htmlspecialchars($manga->thumbnail . "." . $manga->extension) ?>">

                <?php if ($manga->note !== null): ?>
                    <span class="badge-note note-<?= (int) $manga->note ?>">
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

            <a class="link-edit"
               href="<?= $basePath; ?>manga/edit/<?= htmlspecialchars($manga->slug) ?>/<?= (int) $manga->numero ?>">
                Modifier
            </a>

        </article>

    </section>

    <div class="m-t-30">
        <a class="link-section"
           href="<?= $basePath; ?>manga/collection/<?= htmlspecialchars($manga->slug) ?>">
            Back
        </a>
    </div>

</section>