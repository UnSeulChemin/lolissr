<?php

declare(strict_types=1);

use App\DTO\Manga\Responses\ArtbookData;

/** @var ArtbookData $artbook */

$baseUri = view_base_uri();

$slug = rawurlencode($artbook->slug);

$numero = $artbook->numero;

$modifierUrl = $baseUri . 'manga/artbooks/' . $slug . '/modifier/' . $numero;

$deleteUrl = $baseUri . 'manga/artbooks/' . $slug . '/supprimer/' . $numero;

$returnUrl = $baseUri . 'manga/artbooks';

?>

<section class="layout-container dashboard-page">

    <section class="detail-card js-detail-card">

        <figure class="detail-image">

            <div class="detail-image-inner">

                <img src="<?= e($artbook->thumbnailUrl) ?>" alt="<?= e($artbook->artbook) ?>">

            </div>

        </figure>

        <article class="detail-content">

            <div class="detail-row">

                <div class="detail-label">
                    Artbook
                </div>

                <div class="detail-value">
                    <?= e($artbook->artbook) ?>
                </div>

            </div>

            <?php if ($artbook->hasAuteur): ?>

                <div class="detail-row">

                    <div class="detail-label">
                        Auteur
                    </div>

                    <div class="detail-value">
                        <?= e($artbook->auteur) ?>
                    </div>

                </div>

            <?php endif; ?>

            <?php if ($artbook->hasSerie): ?>

                <div class="detail-row">

                    <div class="detail-label">
                        Série
                    </div>

                    <div class="detail-value">
                        <?= e($artbook->serie) ?>
                    </div>

                </div>

            <?php endif; ?>

            <div class="detail-actions">

                <div class="detail-actions-left">
                </div>

                <div class="detail-actions-right">

                    <a class="form-submit" href="<?= e($modifierUrl) ?>">
                        Modifier
                    </a>

                    <button
                        type="button"
                        class="form-submit form-submit-danger js-delete-artbook"
                        data-url="<?= e($deleteUrl) ?>"
                        data-redirect="<?= e($returnUrl) ?>"
                    >
                        Supprimer
                    </button>

                </div>

            </div>

        </article>

    </section>

    <div class="collection-back-wrapper">

        <a class="form-submit collection-back-button" href="<?= e($returnUrl) ?>">
            Retour
        </a>

    </div>

</section>