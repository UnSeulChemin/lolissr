<section class="layout-container">

    <section class="dashboard-header">

        <div class="dashboard-title-box animate-fade-up">

            <h1 class="dashboard-title-main">
                📚 Manga
            </h1>

            <p class="dashboard-subtitle">
                Gère ta collection, ajoute des mangas et accède à tes liens utiles.
            </p>

        </div>

    </section>

    <section class="dashboard-grid card-grid-3 animate-fade-up-stagger">

        <a
            class="card card-small dashboard-panel"
            href="<?= $basePath ?>manga/collection">

            <span class="dashboard-panel-icon">📚</span>

            <span class="dashboard-panel-title">
                Collection
            </span>

            <span class="dashboard-panel-text">
                Voir tous les mangas enregistrés.
            </span>

        </a>

        <a
            class="card card-small dashboard-panel"
            href="<?= $basePath ?>manga/ajouter">

            <span class="dashboard-panel-icon">➕</span>

            <span class="dashboard-panel-title">
                Ajouter
            </span>

            <span class="dashboard-panel-text">
                Ajouter un nouveau manga à la collection.
            </span>

        </a>

        <a
            class="card card-small dashboard-panel"
            href="<?= $basePath ?>manga/lien">

            <span class="dashboard-panel-icon">🔗</span>

            <span class="dashboard-panel-title">
                Liens utiles
            </span>

            <span class="dashboard-panel-text">
                Accéder aux liens utiles liés à la collection.
            </span>

        </a>

    </section>

</section>