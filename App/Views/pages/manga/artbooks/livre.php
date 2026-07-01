<?php

declare(strict_types=1);

if (! isset($artbook))
{
    throw new \RuntimeException(
        'Artbook manquant dans la vue.',
    );
}

$baseUri =
    rtrim(
        (string) ($baseUri ?? ''),
        '/',
    ) . '/';

$slug =
    rawurlencode(
        (string) $artbook->slug,
    );

$numero =
    (int) $artbook->numero;

$thumbnailPath =
    $baseUri
    . 'images/artbook/thumbnail/'
    . $artbook->thumbnail
    . '.'
    . $artbook->extension;

$modifierUrl =
    $baseUri
    . 'manga/artbooks/'
    . $slug
    . '/'
    . $numero
    . '/modifier';

$deleteUrl =
    $baseUri
    . 'manga/artbooks/'
    . $slug
    . '/'
    . $numero
    . '/supprimer';

$returnUrl =
    $baseUri
    . 'manga/artbooks';

$hasAuteur =
    $artbook->auteur !== null
    && trim(
        (string) $artbook->auteur,
    ) !== '';

$hasSerie =
    $artbook->serie !== null
    && trim(
        (string) $artbook->serie,
    ) !== '';

?>

<section class="layout-container dashboard-page">

    <section
        class="
            detail-card
            js-detail-card
        "
    >

        <figure class="detail-image">

            <div class="detail-image-inner">

                <img
                    src="<?= e($thumbnailPath) ?>"
                    alt="<?= e($artbook->artbook) ?>"
                >

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

            <?php if ($hasAuteur): ?>

                <div class="detail-row">

                    <div class="detail-label">
                        Auteur
                    </div>

                    <div class="detail-value">
                        <?= e((string) $artbook->auteur) ?>
                    </div>

                </div>

            <?php endif; ?>

            <?php if ($hasSerie): ?>

                <div class="detail-row">

                    <div class="detail-label">
                        Série
                    </div>

                    <div class="detail-value">
                        <?= e((string) $artbook->serie) ?>
                    </div>

                </div>

            <?php endif; ?>

            <div class="detail-actions">

                <div class="detail-actions-left">
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
                            js-delete-artbook
                        "
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

        <a
            class="
                form-submit
                collection-back-button
            "
            href="<?= e($returnUrl) ?>"
        >

            Retour

        </a>

    </div>

</section>