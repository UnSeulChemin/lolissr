<?php

declare(strict_types=1);

if (! isset($nendoroid))
{
    throw new \RuntimeException(
        'Nendoroid manquante dans la vue.',
    );
}

$baseUri =
    rtrim(
        (string) ($baseUri ?? ''),
        '/',
    ) . '/';

$slug =
    rawurlencode(
        (string) $nendoroid->slug,
    );

$thumbnailPath =
    $baseUri
    . 'images/nendoroids/thumbnail/'
    . $nendoroid->thumbnail
    . '.'
    . $nendoroid->extension;

$modifierUrl =
    $baseUri
    . 'nendoroid/waifus/'
    . $slug
    . '/modifier/'
    . $nendoroid->numero;

$deleteUrl =
    $baseUri
    . 'nendoroid/waifus/'
    . $slug
    . '/supprimer/'
    . $nendoroid->numero;

$redirectUrl =
    $baseUri
    . 'nendoroid/waifus';

$hasCommentaire =
    $nendoroid->commentaire !== null
    && trim((string) $nendoroid->commentaire) !== '';

$commentaire =
    $hasCommentaire
        ? nl2br(
            e(
                (string) $nendoroid->commentaire,
            ),
        )
        : 'Aucun commentaire';

$hasCompany =
    trim((string) $nendoroid->company) !== '';

?>

<section class="layout-container dashboard-page">

    <section class="detail-card">

        <figure class="detail-image">

            <div class="detail-image-inner">

                <img
                    src="<?= e($thumbnailPath) ?>"
                    alt="<?= e($nendoroid->waifu) ?>"
                >

            </div>

        </figure>

        <article class="detail-content">

            <div class="detail-row">

                <div class="detail-label">
                    Waifu
                </div>

                <div class="detail-value">
                    <?= e($nendoroid->waifu) ?>
                </div>

            </div>

            <div class="detail-row">

                <div class="detail-label">
                    Company
                </div>

                <div class="detail-value">

                    <?= $hasCompany
                        ? e($nendoroid->company)
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
                            js-delete-nendoroid
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