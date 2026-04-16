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
            href="<?= $basePath ?>manga/collection">

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
            href="<?= $basePath ?>manga/ajouter">

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
            href="<?= $basePath ?>manga/lien">

            <span class="dashboard-card-icon" aria-hidden="true">🔗</span>

            <span class="dashboard-card-title">
                Liens utiles
            </span>

            <span class="dashboard-card-description">
                Accéder aux liens liés à la collection.
            </span>

        </a>

    </section>

</section>