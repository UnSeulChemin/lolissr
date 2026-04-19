<section class="layout-container animate-fade-up">

    <section class="detail-card">

        <div class="detail-content">

            <h1 class="card-banner">
                🚫 404 — Page introuvable
            </h1>

            <p>
                <?= htmlspecialchars($message ?? 'Le contenu demandé est introuvable.') ?>
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