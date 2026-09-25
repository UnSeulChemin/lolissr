<?php

declare(strict_types=1);

use App\DTO\Common\Responses\ViewData;

/** @var ViewData $view */
/** @var string|null $message */

$message ??=
    'Méthode non autorisée.';

?>

<section class="layout-container dashboard-page">

    <section
        class="
            detail-card
            transition-card
         u-flex u-w-full u-border-box"
    >

        <div class="detail-content u-stack">

            <h1 class="card-banner">
                ⛔ 405 — Méthode non autorisée
            </h1>

            <div class="error-route">

                <span class="error-route-label u-block">
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
                     u-inline-center u-pointer u-semibold"
                    href="<?= e($view->baseUri) ?>"
                >

                    Retour à l’accueil

                </a>

            </div>

        </div>

    </section>

</section>