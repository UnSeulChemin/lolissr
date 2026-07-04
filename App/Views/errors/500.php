<?php

declare(strict_types=1);

/** @var string|null $message */

$message ??=
    'Une erreur interne est survenue.';

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
                ⚠️ 500 — Erreur serveur
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
                    href="<?= e($view->baseUri) ?>"
                >

                    Retour à l’accueil

                </a>

            </div>

        </div>

    </section>

</section>