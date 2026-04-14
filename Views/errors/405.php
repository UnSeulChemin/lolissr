<section class="layout-container animate-fade-up">

    <section class="detail-card">

        <div class="detail-content">

            <h1 class="card-banner">
                ⛔ 405 — Méthode non autorisée
            </h1>

            <p>
                <?= htmlspecialchars($message ?? 'La méthode utilisée n’est pas autorisée.') ?>
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