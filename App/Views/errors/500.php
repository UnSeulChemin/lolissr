<section class="layout-container animate-fade-up">

    <section class="detail-card">

        <div class="detail-content">

            <h1 class="card-banner">
                ⚠️ 500 — Erreur serveur
            </h1>

            <p>
                <?= htmlspecialchars($message ?? 'Une erreur interne est survenue.') ?>
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