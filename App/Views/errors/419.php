<section class="layout-container dashboard-page animate-fade-up">

    <section class="detail-card">

        <div class="detail-content">

            <h1 class="card-banner">
                ⌛ 419 — Session expirée
            </h1>

            <p>
                <?= htmlspecialchars($message ?? 'Session expirée ou requête invalide.') ?>
            </p>

            <div class="detail-actions">

                <a
                    class="form-submit form-submit-secondary"
                    href="<?= $basePath; ?>">

                    Retour à l’accueil

                </a>

            </div>

        </div>

    </section>

</section>