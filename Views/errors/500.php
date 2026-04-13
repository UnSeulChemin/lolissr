<section class="section-content">

    <h1 class="card-banner">
        500 — Erreur serveur
    </h1>

    <p class="m-t-20">
        <?= htmlspecialchars($message ?? 'Une erreur interne est survenue.') ?>
    </p>

    <p class="m-t-20">
        <a href="<?= $basePath; ?>">
            Retour à l’accueil
        </a>
    </p>

</section>