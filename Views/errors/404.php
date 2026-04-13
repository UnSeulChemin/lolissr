<section class="section-content">

    <h1 class="card-banner">
        404 — Page introuvable
    </h1>

    <p class="m-t-20">
        <?= htmlspecialchars($message ?? 'Le contenu demandé est introuvable.') ?>
    </p>

    <p class="m-t-20">
        <a href="<?= $basePath; ?>">
            Retour à l’accueil
        </a>
    </p>

</section>