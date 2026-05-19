<?php

declare(strict_types=1);

$basePath = rtrim($basePath, '/') . '/';

?>

<section class="layout-container dashboard-page">

    <section class="dashboard-header">

        <div class="dashboard-title-box animate-fade-up">

            <h1 class="dashboard-title">
                📚 Manga
            </h1>

            <p class="dashboard-description">
                Gère ta collection, ajoute des mangas et accède à tes liens utiles.
            </p>

        </div>

    </section>

    <section class="dashboard-grid animate-fade-up-stagger">

        <a
            class="dashboard-card"
            href="<?= e($basePath) ?>manga/collection">

            <span class="dashboard-card-icon" aria-hidden="true">📚</span>

            <span class="dashboard-card-title">
                Collection
            </span>

            <span class="dashboard-card-description">
                Voir tous les mangas enregistrés.
            </span>

        </a>

        <a
            class="dashboard-card"
            href="<?= e($basePath) ?>manga/ajouter">

            <span class="dashboard-card-icon" aria-hidden="true">➕</span>

            <span class="dashboard-card-title">
                Ajouter
            </span>

            <span class="dashboard-card-description">
                Ajouter un nouveau manga à la collection.
            </span>

        </a>

        <a
            class="dashboard-card"
            href="<?= e($basePath) ?>manga/lien">

            <span class="dashboard-card-icon" aria-hidden="true">🔗</span>

            <span class="dashboard-card-title">
                Liens utiles
            </span>

            <span class="dashboard-card-description">
                Sites, références et ressources utiles autour des mangas.
            </span>

        </a>

    </section>

</section>