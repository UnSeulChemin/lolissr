<?php

declare(strict_types=1);

$basePath = rtrim(
    (string) ($basePath ?? ''),
    '/'
) . '/';

$message = isset($view['message'])
    && is_string($view['message'])
        ? $view['message']
        : 'Le contenu demandé est introuvable.';

?>

<section class="layout-container dashboard-page animate-fade-up">

    <section class="detail-card">

        <div class="detail-content">

            <h1 class="card-banner">
                🚫 404 — Page introuvable
            </h1>

            <p><?= e($message) ?></p>

            <div class="detail-actions">

                <a
                    class="form-submit form-submit-secondary"
                    href="<?= e($basePath) ?>">

                    Retour à l’accueil

                </a>

            </div>

        </div>

    </section>

</section>