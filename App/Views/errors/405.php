<?php

declare(strict_types=1);

/** @var string|null $message */

$baseUri =
    rtrim(
        $baseUri
        ?? '',
        '/',
    ) . '/';

$message ??=
    'Méthode non autorisée.';

?>

<section class="layout-container dashboard-page">

    <section
        class="
            detail-card
            transition-card
        "
    >

        <div class="detail-content">

            <h1 class="card-banner">
                ⛔ 405 — Méthode non autorisée
            </h1>

            <div class="error-route">

                <span class="error-route-label">
                    Erreur
                </span>

                <span class="error-route-path">
                    <?= e($message) ?>
                </span>

            </div>

            <div class="detail-actions">

                <a
                    class="
                        form-submit
                        form-submit-secondary
                    "
                    href="<?= e($baseUri) ?>"
                >

                    Retour à l’accueil

                </a>

            </div>

        </div>

    </section>

</section>