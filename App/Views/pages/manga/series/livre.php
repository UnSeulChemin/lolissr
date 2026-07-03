<?php

declare(strict_types=1);

use App\DTO\Manga\Responses\MangaData;

/** @var MangaData $manga */

$baseUri = view_base_uri();

$slug = rawurlencode($manga->slug);

$numero = $manga->numero;

$modifierUrl = $baseUri . 'manga/series/' . $slug . '/modifier/' . $numero;

$deleteUrl = $baseUri . 'manga/series/' . $slug . '/supprimer/' . $numero;

$redirectUrl = $baseUri . 'manga/series/' . $slug;

$updateReadStatusUrl = $baseUri . 'manga/ajax/update-read-status/' . $slug . '/' . $numero;

$isLu = $manga->lu;

$readStatusLabel = $isLu ? 'Marquer comme non lu' : 'Marquer comme lu';

$commentaire = $manga->hasCommentaire ? nl2br(e($manga->commentaire)) : 'Aucun commentaire';

?>

<section class="layout-container dashboard-page">

    <section
        class="
            detail-card
            js-detail-card
        "
        data-slug="<?= e($slug) ?>"
        data-numero="<?= $numero ?>"
        data-base-path="<?= e($baseUri) ?>"
        data-jacquette="<?= $manga->jacquette ?? 1 ?>"
        data-livre-note="<?= $manga->livreNote ?? 1 ?>"
    >

        <figure class="detail-image">

            <div class="detail-image-inner">
                <img src="<?= e($manga->thumbnailUrl) ?>" alt="<?= e($manga->livre) ?>">
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

                    <?= $manga->hasEditeur
                        ? e($manga->editeur)
                        : 'Non renseigné'
?>

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
                        STR_PAD_LEFT,
                    ) ?>

                </div>

            </div>


            <div class="detail-row">

                <div class="detail-label">
                    Statut
                </div>

                <div class="detail-value">
                    <?= $manga->statusLabel ?>
                </div>

            </div>


            <!-- Jacquette -->

            <div class="detail-row">

                <div class="detail-label">
                    Jacquette
                </div>

                <div class="detail-value detail-value-notes">

                    <div
                        class="
                            js-note-group
                            <?= $manga->isPerfectJacquette
                                ? 'perfect-score-group'
                                : ''
                            ?>
                        "
                        data-field="jacquette"
                    >

                        <?php for (
                            $note = 1;
                            $note <= 5;
                            $note++
                        ): ?>

                            <button
                                class="
                                    js-note-button
                                    <?= ($manga->jacquette === $note)
                                        ? 'active'
                                        : ''
                                    ?>
                                "
                                type="button"
                                data-value="<?= $note ?>"
                            >

                                <?= $note ?>

                            </button>

                        <?php endfor; ?>

                    </div>

                </div>

            </div>

            <!-- Livre Note -->

            <div class="detail-row">

                <div class="detail-label">
                    Livre
                </div>

                <div class="detail-value detail-value-notes">

                    <div class="js-note-group <?= $manga->isPerfectLivre ? 'perfect-score-group' : '' ?>"
                        data-field="livreNote"
                    >

                        <?php for ($note = 1; $note <= 5; $note++): ?>

                            <button class="js-note-button <?= ($manga->livreNote === $note) ? 'active' : '' ?>"
                                type="button"
                                data-value="<?= $note ?>"
                            >
                                <?= $note ?>
                            </button>

                        <?php endfor; ?>

                    </div>

                </div>

            </div>

            <!-- Note totale -->

            <div class="detail-row">

                <div class="detail-label">
                    Note totale
                </div>

                <div class="detail-value" id="js-note-total">

                    <?= ($manga->jacquette ?? 0) + ($manga->livreNote ?? 0) ?>/10

                </div>

            </div>

            <!-- COMMENTAIRE -->

            <div class="detail-row detail-row-comment">

                <div class="detail-label">
                    Commentaire
                </div>

                <div class="detail-value detail-comment-box <?= ! $manga->hasCommentaire ? 'is-empty' : '' ?>">
                    <?= $commentaire ?>
                </div>

            </div>

            <!-- ACTIONS -->

            <div class="detail-actions">

                <div class="detail-actions-left">

                    <button type="button" class="js-read-status-button <?= $isLu ? 'active' : '' ?>"
                        data-url="<?= e($updateReadStatusUrl) ?>"
                        data-read-status="<?= $isLu ? '1' : '0' ?>"
                        title="<?= e($readStatusLabel) ?>"
                        aria-label="<?= e($readStatusLabel) ?>"
                    >
                        <svg class="lu-icon" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M7 3C6.45 3 6 3.45 6 4V21L12 17L18 21V4C18 3.45 17.55 3 17 3H7Z"/>
                        </svg>

                    </button>

                </div>


                <div class="detail-actions-right">

                    <a class="form-submit" href="<?= e($modifierUrl) ?>">
                        Modifier
                    </a>

                    <button
                        type="button"
                        class="form-submit form-submit-danger js-delete-manga"
                        data-url="<?= e($deleteUrl) ?>"
                        data-redirect="<?= e($redirectUrl) ?>"
                    >
                        Supprimer
                    </button>

                </div>

            </div>

        </article>

    </section>


    <div class="collection-back-wrapper">

        <a class="form-submit collection-back-button" href="<?= e($redirectUrl) ?>">
            Retour
        </a>

    </div>

</section>
