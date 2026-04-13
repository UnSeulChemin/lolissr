<section class="section-content">

    <h1 class="card-banner">
        405 — Méthode non autorisée
    </h1>

    <p class="m-t-20">
        <?= htmlspecialchars($message ?? 'La méthode utilisée n’est pas autorisée.') ?>
    </p>

    <p class="m-t-20">
        <a href="<?= $basePath; ?>">
            Retour à l’accueil
        </a>
    </p>

</section>