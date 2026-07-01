<?php

declare(strict_types=1);

if (! isset($peluche))
{
    throw new \RuntimeException(
        'Peluche manquante dans la vue.',
    );
}

$baseUri =
    rtrim(
        (string) ($baseUri ?? ''),
        '/',
    ) . '/';

$slug =
    rawurlencode(
        (string) $peluche->slug,
    );

$thumbnailPath =
    $baseUri
    . 'images/peluche/thumbnail/'
    . $peluche->thumbnail
    . '.'
    . $peluche->extension;

$modifierUrl =
    $baseUri
    . 'peluche/waifus/'
    . $slug
    . '/modifier/'
    . $peluche->numero;

$deleteUrl =
    $baseUri
    . 'peluche/waifus/'
    . $slug
    . '/supprimer/'
    . $peluche->numero;

$redirectUrl =
    $baseUri
    . 'peluche/waifus';

$hasCommentaire =
    $peluche->commentaire !== null
    && trim((string) $peluche->commentaire) !== '';

$commentaire =
    $hasCommentaire
        ? nl2br(
            e(
                (string) $peluche->commentaire,
            ),
        )
        : 'Aucun commentaire';

$hasCompany =
    trim((string) $peluche->company) !== '';

?>

<section class="layout-container dashboard-page">

    <section class="detail-card">

        <figure class="detail-image">

            <div class="detail-image-inner">

                <img
                    src="<?= e($thumbnailPath) ?>"
                    alt="<?= e($peluche->waifu) ?>"
                >

            </div>

        </figure>

        <article class="detail-content">

            <div class="detail-row">

                <div class="detail-label">
                    Waifu
                </div>

                <div class="detail-value">
                    <?= e($peluche->waifu) ?>
                </div>

            </div>

            <div class="detail-row">

                <div class="detail-label">
                    Company
                </div>

                <div class="detail-value">

                    <?= $hasCompany
                        ? e($peluche->company)
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
                            js-delete-peluche
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