<?php

declare(strict_types=1);

$slug = rawurlencode((string) $manga->slug);

$numero = (int) $manga->numero;

$thumbnailPath = $basePath
    . 'public/images/mangas/thumbnail/'
    . $manga->thumbnail
    . '.'
    . $manga->extension;

$modifierUrl = $basePath
    . 'manga/series/modifier/'
    . $slug
    . '/'
    . $numero;

$deleteUrl = $basePath
    . 'manga/series/supprimer/'
    . $slug
    . '/'
    . $numero;

$redirectUrl = $basePath
    . 'manga/series/'
    . $slug;

$updateLuUrl = $basePath
    . 'manga/ajax/update-lu/'
    . $slug
    . '/'
    . $numero;

$isLu = (int) ($manga->lu ?? 0) === 1;

$luLabel = $isLu
    ? 'Marquer comme non lu'
    : 'Marquer comme lu';

$statutLabel = ($manga->statut ?? 'en_cours') === 'termine'
    ? 'Terminé'
    : 'En cours';

$noteTotal = $manga->note !== null
    ? (int) $manga->note . '/10'
    : 'Non calculée';

$commentaire = !empty($manga->commentaire)
    ? nl2br(e($manga->commentaire))
    : 'Aucun commentaire';

?>

<section class="layout-container dashboard-page">

    <section
        class="detail-card animate-fade-up js-detail-card"
        data-slug="<?= e($slug) ?>"
        data-numero="<?= $numero ?>"
        data-base-path="<?= e($basePath) ?>"
        data-jacquette="<?= $manga->jacquette !== null ? (int) $manga->jacquette : '' ?>"
        data-livre-note="<?= $manga->livre_note !== null ? (int) $manga->livre_note : '' ?>">

        <figure class="detail-image">

            <div class="detail-image-inner">

                <img
                    src="<?= e($thumbnailPath) ?>"
                    alt="<?= e($manga->livre) ?>">

            </div>

        </figure>

        <article class="detail-content">

            <div class="detail-row">

                <div class="detail-label">
                    Livre
                </div>

                <div class="detail-value">
                    <?= e($manga->livre) ?>
                </div>

            </div>

            <div class="detail-row">

                <div class="detail-label">
                    Éditeur
                </div>

                <div class="detail-value">

                    <?= !empty($manga->editeur)
                        ? e($manga->editeur)
                        : 'Non renseigné' ?>

                </div>

            </div>

            <div class="detail-row">

                <div class="detail-label">
                    Tome
                </div>

                <div class="detail-value">

                    <?= str_pad(
                        (string) $numero,
                        2,
                        '0',
                        STR_PAD_LEFT
                    ) ?>

                </div>

            </div>

            <div class="detail-row">

                <div class="detail-label">
                    Statut
                </div>

                <div class="detail-value">

                    <?= $statutLabel ?>

                </div>

            </div>

            <div class="detail-row">

                <div class="detail-label">
                    Jacquette
                </div>

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

                <div class="detail-label">
                    État du livre
                </div>

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

                <div class="detail-label">
                    Note totale
                </div>

                <div
                    class="detail-value"
                    id="ajax-note-total">

                    <?= e($noteTotal) ?>

                </div>

            </div>

            <div class="detail-row detail-row-comment">

                <div class="detail-label">
                    Commentaire
                </div>

                <div class="detail-value detail-comment-box <?= empty($manga->commentaire) ? 'is-empty' : '' ?>">

                    <?= $commentaire ?>

                </div>

            </div>

            <div class="detail-actions">

                <div class="detail-actions-left">

                    <button
                        type="button"
                        class="ajax-lu-button <?= $isLu ? 'active' : '' ?>"
                        data-url="<?= e($updateLuUrl) ?>"
                        data-lu="<?= $isLu ? '1' : '0' ?>"
                        title="<?= e($luLabel) ?>"
                        aria-label="<?= e($luLabel) ?>">

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
                        href="<?= e($modifierUrl) ?>">

                        Modifier

                    </a>

                    <button
                        type="button"
                        class="form-submit form-submit-danger js-delete-manga"
                        data-url="<?= e($deleteUrl) ?>"
                        data-redirect="<?= e($redirectUrl) ?>">

                        Supprimer

                    </button>

                </div>

            </div>

        </article>

    </section>

    <div class="collection-back-wrapper">

        <a
            class="form-submit collection-back-button"
            href="<?= e($redirectUrl) ?>">

            Retour

        </a>

    </div>

</section>