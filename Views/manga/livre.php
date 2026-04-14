<section class="section-content">

    <section class="manga-detail-card">

        <figure class="manga-detail-image">

            <div class="manga-detail-image-inner">

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
                    <span class="badge-note <?= htmlspecialchars($noteClass) ?>">
                        <?= (int) $manga->note ?>
                    </span>
                <?php endif; ?>

            </div>

        </figure>

        <article class="manga-detail-content">

            <div class="detail-row">
                <div class="detail-label">Livre</div>
                <div class="detail-value"><?= htmlspecialchars($manga->livre) ?></div>
            </div>

            <div class="detail-row">
                <div class="detail-label">Tome</div>
                <div class="detail-value">
                    <?= str_pad((string) ((int) $manga->numero), 2, '0', STR_PAD_LEFT) ?>
                </div>
            </div>

            <div class="detail-row">
                <div class="detail-label">Jacquette</div>
                <div class="detail-value">
                    <?= $manga->jacquette !== null ? (int) $manga->jacquette . '/5' : 'Non noté' ?>
                </div>
            </div>

            <div class="detail-row">
                <div class="detail-label">État du livre</div>
                <div class="detail-value">
                    <?= $manga->livre_note !== null ? (int) $manga->livre_note . '/5' : 'Non noté' ?>
                </div>
            </div>

            <div class="detail-row">
                <div class="detail-label">Note totale</div>
                <div class="detail-value">
                    <?= $manga->note !== null ? (int) $manga->note . '/10' : 'Non calculée' ?>
                </div>
            </div>

            <div class="detail-row detail-row-comment">
                <div class="detail-label">Commentaire</div>
                <div class="detail-value detail-comment">
                    <?= !empty($manga->commentaire)
                        ? nl2br(htmlspecialchars($manga->commentaire))
                        : 'Aucun commentaire' ?>
                </div>
            </div>

            <div class="detail-actions">
                <a class="manga-form-submit manga-back-button"
                   href="<?= $basePath; ?>manga/update/<?= rawurlencode($manga->slug) ?>/<?= (int) $manga->numero ?>">
                    Modifier
                </a>
            </div>

        </article>

    </section>

    <div class="m-t-30">
        <a class="link-section"
           href="<?= $basePath; ?>manga/collection/<?= rawurlencode($manga->slug) ?>">
            Retour
        </a>
    </div>

</section>