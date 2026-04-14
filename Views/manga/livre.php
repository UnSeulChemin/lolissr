<section class="layout-container">

    <section class="detail-card animate-fade-up">

        <figure class="detail-image">

            <div class="detail-image-inner">

                <img
                    src="<?= $basePath; ?>public/images/mangas/thumbnail/<?= htmlspecialchars($manga->thumbnail . '.' . $manga->extension) ?>"
                    alt="<?= htmlspecialchars($manga->livre) ?>">

            </div>

        </figure>

        <article class="detail-content">

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

                <div class="detail-value detail-comment-box <?= empty($manga->commentaire) ? 'is-empty' : '' ?>">
                    <?= !empty($manga->commentaire)
                        ? nl2br(htmlspecialchars($manga->commentaire))
                        : 'Aucun commentaire' ?>
                </div>
            </div>

            <div class="detail-actions">
                <a
                    class="form-submit"
                    href="<?= $basePath; ?>manga/update/<?= rawurlencode($manga->slug) ?>/<?= (int) $manga->numero ?>">
                    Modifier
                </a>
            </div>

        </article>

    </section>

    <div class="collection-back-wrapper">
        <a
            class="form-submit collection-back-button"
            href="<?= $basePath; ?>manga/serie/<?= rawurlencode($manga->slug) ?>">
            Retour
        </a>
    </div>

</section>