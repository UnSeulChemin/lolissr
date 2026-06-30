<?php

declare(strict_types=1);

if (! isset($figurine))
{
    throw new \RuntimeException(
        'Figurine manquante dans la vue.',
    );
}

$baseUri =
    rtrim(
        (string) ($baseUri ?? ''),
        '/',
    ) . '/';

$slug =
    rawurlencode(
        (string) $figurine->slug,
    );

$thumbnailPath =
    $baseUri
    . 'images/figurine/thumbnail/'
    . $figurine->thumbnail
    . '.'
    . $figurine->extension;

$modifierUrl =
    $baseUri
    . 'figurine/waifus/'
    . $slug
    . '/modifier/'
    . $figurine->numero;

$deleteUrl =
    $baseUri
    . 'figurine/waifus/'
    . $slug
    . '/supprimer/'
    . $figurine->numero;

$redirectUrl =
    $baseUri
    . 'figurine/waifus';

$hasCommentaire =
    $figurine->commentaire !== null
    && trim((string) $figurine->commentaire) !== '';

$commentaire =
    $hasCommentaire
        ? nl2br(
            e(
                (string) $figurine->commentaire,
            ),
        )
        : 'Aucun commentaire';

$hasCompany =
    trim((string) $figurine->company) !== '';

?>

<section class="layout-container dashboard-page">

    <section class="detail-card">

        <figure class="detail-image">

            <div class="detail-image-inner">

                <img
                    src="<?= e($thumbnailPath) ?>"
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
                    Company
                </div>

                <div class="detail-value">

                    <?= $hasCompany
                        ? e($figurine->company)
                        : 'Non renseignée'
                    ?>

                </div>

            </div>

            <div
                class="
                    detail-row
                    detail-row-comment
                "
            >

                <div class="detail-label">
                    Commentaire
                </div>

                <div
                    class="
                        detail-value
                        detail-comment-box
                        <?= !$hasCommentaire ? 'is-empty' : '' ?>
                    "
                >

                    <?= $commentaire ?>

                </div>

            </div>

            <div class="detail-actions">

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