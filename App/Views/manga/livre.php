<section class="layout-container dashboard-page">

    <section
        class="detail-card animate-fade-up js-detail-card"
        data-slug="<?= rawurlencode($manga->slug) ?>"
        data-numero="<?= (int) $manga->numero ?>"
        data-base-path="<?= $basePath ?>"
        data-jacquette="<?= $manga->jacquette !== null ? (int) $manga->jacquette : '' ?>"
        data-livre-note="<?= $manga->livre_note !== null ? (int) $manga->livre_note : '' ?>">

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

                <div class="detail-value detail-value-notes">
                    <div
                        class="ajax-note-group"
                        data-field="jacquette">

                        <?php for ($note = 1; $note <= 5; $note++): ?>
                            <button
                                class="ajax-note-button <?= ($manga->jacquette !== null && (int) $manga->jacquette === $note) ? 'active' : '' ?>"
                                type="button"
                                data-value="<?= $note ?>">
                                <?= $note ?>
                            </button>
                        <?php endfor; ?>

                    </div>
                </div>
            </div>

            <div class="detail-row">
                <div class="detail-label">État du livre</div>

                <div class="detail-value detail-value-notes">
                    <div
                        class="ajax-note-group"
                        data-field="livre_note">

                        <?php for ($note = 1; $note <= 5; $note++): ?>
                            <button
                                class="ajax-note-button <?= ($manga->livre_note !== null && (int) $manga->livre_note === $note) ? 'active' : '' ?>"
                                type="button"
                                data-value="<?= $note ?>">
                                <?= $note ?>
                            </button>
                        <?php endfor; ?>

                    </div>
                </div>
            </div>

            <div class="detail-row">
                <div class="detail-label">Note totale</div>
                <div
                    class="detail-value"
                    id="ajax-note-total">
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

                <div class="detail-actions-left">

                    <button
                        type="button"
                        class="ajax-lu-button <?= (int) ($manga->lu ?? 0) === 1 ? 'active' : '' ?>"
                        data-url="<?= $basePath; ?>manga/ajax/update-lu/<?= rawurlencode($manga->slug) ?>/<?= (int) $manga->numero ?>"
                        data-lu="<?= (int) ($manga->lu ?? 0) ?>"
                        title="<?= (int) ($manga->lu ?? 0) === 1 ? 'Marquer comme non lu' : 'Marquer comme lu' ?>"
                        aria-label="<?= (int) ($manga->lu ?? 0) === 1 ? 'Marquer comme non lu' : 'Marquer comme lu' ?>">

                        <svg
                            class="lu-icon"
                            viewBox="0 0 24 24"
                            aria-hidden="true">

                            <path d="M7 3C6.45 3 6 3.45 6 4V21L12 17L18 21V4C18 3.45 17.55 3 17 3H7Z"/>

                        </svg>

                    </button>

                </div>

                <div class="detail-actions-right">

                    <a
                        class="form-submit"
                        href="<?= $basePath; ?>manga/modifier/<?= rawurlencode($manga->slug) ?>/<?= (int) $manga->numero ?>">
                        Modifier
                    </a>

                    <button
                        type="button"
                        class="form-submit form-submit-danger js-delete-manga"
                        data-url="<?= $basePath; ?>manga/ajax/supprimer/<?= rawurlencode($manga->slug) ?>/<?= (int) $manga->numero ?>"
                        data-redirect="<?= $basePath; ?>manga/serie/<?= rawurlencode($manga->slug) ?>">
                        Supprimer
                    </button>

                </div>

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