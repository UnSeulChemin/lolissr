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
    'La méthode utilisée n’est pas autorisée.';

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