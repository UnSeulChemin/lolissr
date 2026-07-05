<?php

declare(strict_types=1);

use App\DTO\Common\Responses\ViewData;
use App\DTO\Figurine\Responses\FigurineData;

/** @var ViewData $view */
/** @var FigurineData $figurine */

$slug = rawurlencode($figurine->slug);

$modifierUrl = $view->baseUri . 'figurine/waifus/' . $slug . '/modifier/' . $figurine->numero;

$deleteUrl = $view->baseUri . 'figurine/waifus/' . $slug . '/supprimer/' . $figurine->numero;

$redirectUrl = $view->baseUri . 'figurine/waifus';

$hasCommentaire = $figurine->commentaire !== null
    && trim($figurine->commentaire) !== '';

$commentaire = $hasCommentaire
    ? nl2br(e($figurine->commentaire))
    : 'Aucun commentaire';

$updateCollectStatusUrl = $view->baseUri
    . 'figurine/ajax/update-collect-status/'
    . $slug
    . '/'
    . $figurine->numero;

$isCollected = $figurine->collect;

$collectStatusLabel = $isCollected
    ? 'Retirer de la collection'
    : 'Ajouter à la collection';

?>

<section class="layout-container dashboard-page">

    <section class="detail-card">

        <figure class="detail-image">

            <div class="detail-image-inner">

                <img
                    src="<?= e($figurine->thumbnailUrl) ?>"
                    alt="<?= e($figurine->waifu) ?>"
                >

            </div>

        </figure>

        <article class="detail-content">

            <div class="detail-row">

                <div class="detail-label">
                    Waifu
                </div>

                <div class="detail-value">
                    <?= e($figurine->waifu) ?>
                </div>

            </div>

            <div class="detail-row">

                <div class="detail-label">
                    Source
                </div>

                <div class="detail-value">

                    <?= $figurine->origin !== ''
                        ? e($figurine->origin)
                        : 'Non renseignée' ?>

                </div>

            </div>

            <div class="detail-row">

                <div class="detail-label">
                    Échelle
                </div>

                <div class="detail-value">

                    <?= $figurine->scale !== ''
                        ? e($figurine->scale)
                        : 'Non renseignée' ?>

                </div>

            </div>

            <div class="detail-row">

                <div class="detail-label">
                    Hauteur
                </div>

                <div class="detail-value">

                    <?= $figurine->height_cm !== null
                        ? e((string) $figurine->height_cm) . ' cm'
                        : 'Non renseignée' ?>

                </div>

            </div>

            <div class="detail-row">

                <div class="detail-label">
                    Entreprise
                </div>

                <div class="detail-value">

                    <?= $figurine->company !== ''
                        ? e($figurine->company)
                        : 'Non renseignée' ?>

                </div>

            </div>

            <div class="detail-row">

                <div class="detail-label">
                    Date de sortie
                </div>

                <div class="detail-value">

                    <?= $figurine->release_date !== null
                        ? e($figurine->release_date)
                        : 'Non renseignée' ?>

                </div>

            </div>

            <div class="detail-row detail-row-comment">

                <div class="detail-label">
                    Commentaire
                </div>

                <div class="detail-value detail-comment-box <?= ! $hasCommentaire ? 'is-empty' : '' ?>">

                    <?= $commentaire ?>

                </div>

            </div>

            <div class="detail-actions">

                <div class="detail-actions-left">

                    <button
                        type="button"
                        class="js-collect-status-button <?= $isCollected ? 'active' : '' ?>"
                        data-url="<?= e($updateCollectStatusUrl) ?>"
                        data-slug="<?= e($slug) ?>"
                        data-numero="<?= $figurine->numero ?>"
                        data-collect-status="<?= $isCollected ? '1' : '0' ?>"
                        title="<?= e($collectStatusLabel) ?>"
                        aria-label="<?= e($collectStatusLabel) ?>"
                    >

                        <svg
                            class="collect-icon"
                            viewBox="0 0 24 24"
                            aria-hidden="true"
                        >
                            <path d="M12 2.5L14.9 8.63L21.5 9.27L16.5 13.8L17.9 20.3L12 17L6.1 20.3L7.5 13.8L2.5 9.27L9.1 8.63L12 2.5Z"/>
                        </svg>

                    </button>

                </div>

                <div class="detail-actions-right">

                    <a
                        class="form-submit"
                        href="<?= e($modifierUrl) ?>"
                    >
                        Modifier
                    </a>

                    <button
                        type="button"
                        class="
                            form-submit
                            form-submit-danger
                            js-delete-figurine
                        "
                        data-url="<?= e($deleteUrl) ?>"
                        data-redirect="<?= e($redirectUrl) ?>"
                        data-slug="<?= e($slug) ?>"
                    >
                        Supprimer
                    </button>

                </div>

            </div>

        </article>

    </section>

    <div class="collection-back-wrapper">

        <a
            class="
                form-submit
                collection-back-button
            "
            href="<?= e($redirectUrl) ?>"
        >
            Retour
        </a>

    </div>

</section>