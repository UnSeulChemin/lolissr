<?php

declare(strict_types=1);

/** @var string $baseUri */
/** @var string|null $message */

$baseUri =
    rtrim(
        $baseUri,
        '/',
    ) . '/';

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

            <p>
                <?= e($message) ?>
            </p>

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