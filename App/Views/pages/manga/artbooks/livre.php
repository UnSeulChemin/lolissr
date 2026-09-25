<?php

declare(strict_types=1);

use App\DTO\Common\Responses\ViewData;
use App\DTO\Manga\Responses\ArtbookData;

/** @var ViewData $view */
/** @var ArtbookData $artbook */

$slug = rawurlencode($artbook->slug);

$numero = $artbook->numero;

$modifierUrl = $view->baseUri . 'manga/artbooks/' . $slug . '/modifier/' . $numero;

$deleteUrl = $view->baseUri . 'manga/artbooks/' . $slug . '/supprimer/' . $numero;

$returnUrl = $view->baseUri . 'manga/artbooks';

$updateReadStatusUrl = $view->baseUri . 'manga/ajax/artbook/update-read-status/' . $slug . '/' . $numero;

$isLu = $artbook->lu;

$readStatusLabel = $isLu ? 'Marquer comme non lu' : 'Marquer comme lu';

$hasCommentaire = $artbook->commentaire !== null
    && trim($artbook->commentaire) !== '';

$commentaire = $hasCommentaire
    ? nl2br(e($artbook->commentaire))
    : 'Aucun commentaire';

?>

<section class="layout-container dashboard-page">

    <section class="detail-card js-detail-card u-flex u-w-full u-border-box">

        <figure class="detail-image u-flex u-justify-center">

            <div class="detail-image-inner u-w-full">

                <img
                    src="<?= e($artbook->thumbnailUrl) ?>"
                    alt="<?= e($artbook->artbook) ?>"
                >

            </div>

        </figure>

        <article class="detail-content u-stack">

            <div class="detail-row u-grid">

                <div class="detail-label">
                    Artbook
                </div>

                <div class="detail-value">
                    <?= e($artbook->artbook) ?>
                </div>

            </div>

            <?php if ($artbook->hasAuteur): ?>

                <div class="detail-row u-grid">

                    <div class="detail-label">
                        Auteur
                    </div>

                    <div class="detail-value">
                        <?= e($artbook->auteur) ?>
                    </div>

                </div>

            <?php endif; ?>

            <?php if ($artbook->hasSerie): ?>

                <div class="detail-row u-grid">

                    <div class="detail-label">
                        Série
                    </div>

                    <div class="detail-value">
                        <?= e($artbook->serie) ?>
                    </div>

                </div>

            <?php endif; ?>

            <div class="detail-row u-grid">

                <div class="detail-label">
                    Entreprise
                </div>

                <div class="detail-value">

                    <?= $artbook->company !== ''
                        ? e($artbook->company)
                        : 'Non renseignée'
                    ?>

                </div>

            </div>

            <div class="detail-row u-grid">

                <div class="detail-label">
                    Date de sortie
                </div>

                <div class="detail-value">

                    <?= $artbook->releaseDate !== null
                        ? e($artbook->releaseDate)
                        : 'Non renseignée'
                    ?>

                </div>

            </div>

            <!-- COMMENTAIRE -->

            <div class="detail-row detail-row-comment u-grid">

                <div class="detail-label">
                    Commentaire
                </div>

                <div class="detail-value detail-comment-box <?= ! $hasCommentaire ? 'is-empty' : '' ?>">

                    <?= $commentaire ?>

                </div>

            </div>

            <!-- ACTIONS -->

            <div class="detail-actions">

                <div class="detail-actions-left">

                    <button
                        type="button"
                        class="status-toggle js-read-status-button <?= $isLu ? 'active' : '' ?> u-inline-center u-pointer"
                        data-url="<?= e($updateReadStatusUrl) ?>"
                        data-read-status="<?= $isLu ? '1' : '0' ?>"
                        title="<?= e($readStatusLabel) ?>"
                        aria-label="<?= e($readStatusLabel) ?>"
                    >
                        <svg class="status-toggle-icon status-toggle-icon--read lu-icon" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M7 3C6.45 3 6 3.45 6 4V21L12 17L18 21V4C18 3.45 17.55 3 17 3H7Z"/>
                        </svg>
                    </button>

                </div>

                <div class="detail-actions-right u-row-center">

                    <a
                        class="form-submit u-inline-center u-pointer u-semibold"
                        href="<?= e($modifierUrl) ?>"
                    >
                        Modifier
                    </a>

                    <button
                        type="button"
                        class="
                            form-submit
                            form-submit-danger
                            js-delete-artbook
                         u-inline-center u-pointer u-semibold"
                        data-url="<?= e($deleteUrl) ?>"
                        data-redirect="<?= e($returnUrl) ?>"
                    >
                        Supprimer
                    </button>

                </div>

            </div>

        </article>

    </section>

    <div class="collection-back-wrapper u-row-center">

        <a
            class="
                form-submit
                collection-back-button
             u-inline-center u-pointer u-semibold"
            href="<?= e($returnUrl) ?>"
        >
            Retour
        </a>

    </div>

</section>