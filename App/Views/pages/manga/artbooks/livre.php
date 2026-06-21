<?php

declare(strict_types=1);

if (!isset($artbook))
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

$thumbnailPath =
    $baseUri
    . 'images/artbooks/thumbnail/'
    . $artbook->thumbnail
    . '.'
    . $artbook->extension;

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

$returnUrl =
    $baseUri
    . 'manga/artbooks';

?>

<section class="layout-container dashboard-page">

    <section class="detail-card">

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

            <div class="detail-row">

                <div class="detail-label">
                    Auteur
                </div>

                <div class="detail-value">

                    <?= $hasAuteur
                        ? e(
                            (string) $artbook->auteur,
                        )
                        : 'Non renseigné'
                    ?>

                </div>

            </div>

            <div class="detail-row">

                <div class="detail-label">
                    Série
                </div>

                <div class="detail-value">

                    <?= $hasSerie
                        ? e(
                            (string) $artbook->serie,
                        )
                        : 'Non renseignée'
                    ?>

                </div>

            </div>

            <div class="detail-row">

                <div class="detail-label">
                    Slug
                </div>

                <div class="detail-value">
                    <?= e($artbook->slug) ?>
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