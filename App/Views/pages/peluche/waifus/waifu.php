<?php

declare(strict_types=1);

use App\DTO\Common\Responses\ViewData;
use App\DTO\Peluche\Responses\PelucheData;

/** @var ViewData $view */
/** @var PelucheData $peluche */

$slug = rawurlencode($peluche->slug);

$modifierUrl = $view->baseUri . 'peluche/waifus/' . $slug . '/modifier/' . $peluche->numero;

$deleteUrl = $view->baseUri . 'peluche/waifus/' . $slug . '/supprimer/' . $peluche->numero;

$redirectUrl = $view->baseUri . 'peluche/waifus';

$hasCommentaire = $peluche->commentaire !== null
    && trim($peluche->commentaire) !== '';

$commentaire = $hasCommentaire
    ? nl2br(e($peluche->commentaire))
    : 'Aucun commentaire';

$updateCollectStatusUrl = $view->baseUri
    . 'peluche/ajax/update-collect-status/'
    . $slug
    . '/'
    . $peluche->numero;

$isCollected = $peluche->collect;

$collectStatusLabel = $isCollected
    ? 'Retirer de la collection'
    : 'Ajouter à la collection';

?>

<section class="layout-container dashboard-page">

    <section class="detail-card u-flex u-w-full u-border-box">

        <figure class="detail-image u-flex u-justify-center">

            <div class="detail-image-inner u-w-full">

                <?php if ($peluche->thumbnailUrl !== null): ?>

                    <img
                        src="<?= e($peluche->thumbnailUrl) ?>"
                        alt="<?= e($peluche->waifu) ?>"
                    >

                <?php endif; ?>

            </div>

        </figure>

        <article class="detail-content u-stack">

            <div class="detail-row u-grid">

                <div class="detail-label">
                    Waifu
                </div>

                <div class="detail-value">
                    <?= e($peluche->waifu) ?>
                </div>

            </div>

            <div class="detail-row u-grid">

                <div class="detail-label">
                    Source
                </div>

                <div class="detail-value">

                    <?= $peluche->origin !== ''
                        ? e($peluche->origin)
                        : 'Non renseignée' ?>

                </div>

            </div>

            <div class="detail-row u-grid">

                <div class="detail-label">
                    Entreprise
                </div>

                <div class="detail-value">

                    <?= $peluche->company !== ''
                        ? e($peluche->company)
                        : 'Non renseignée' ?>

                </div>

            </div>

            <div class="detail-row u-grid">

                <div class="detail-label">
                    Date de sortie
                </div>

                <div class="detail-value">

                    <?= $peluche->release_date !== null
                        ? e($peluche->release_date)
                        : 'Non renseignée' ?>

                </div>

            </div>

            <div class="detail-row detail-row-comment u-grid">

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
                        class="
                            status-toggle js-collect-status-button
                            js-peluche-collect-status-button
                            <?= $isCollected ? 'active' : '' ?>
                         u-inline-center u-pointer"
                        data-url="<?= e($updateCollectStatusUrl) ?>"
                        data-slug="<?= e($slug) ?>"
                        data-numero="<?= $peluche->numero ?>"
                        data-collect-status="<?= $isCollected ? '1' : '0' ?>"
                        title="<?= e($collectStatusLabel) ?>"
                        aria-label="<?= e($collectStatusLabel) ?>"
                    >

                        <svg
                            class="status-toggle-icon collect-icon"
                            viewBox="0 0 24 24"
                            aria-hidden="true"
                        >
                            <path d="M12 2.5L14.9 8.63L21.5 9.27L16.5 13.8L17.9 20.3L12 17L6.1 20.3L7.5 13.8L2.5 9.27L9.1 8.63L12 2.5Z"/>
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
                            js-delete-peluche
                         u-inline-center u-pointer u-semibold"
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

    <div class="collection-back-wrapper u-row-center">

        <a
            class="
                form-submit
                collection-back-button
             u-inline-center u-pointer u-semibold"
            href="<?= e($redirectUrl) ?>"
        >
            Retour
        </a>

    </div>

</section>