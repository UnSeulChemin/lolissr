<section class="section-content">

    <section class="form-box">

        <h1 class="form-title">
            Ajouter un manga
        </h1>

        <?php if (!empty($_SESSION['success'])): ?>
            <div class="alert-success">
                <?= htmlspecialchars($_SESSION['success']) ?>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <form class="form-add"
              action="<?= $basePath; ?>manga/ajouterTraitement"
              method="post"
              enctype="multipart/form-data">

            <div class="form-row">

                <label for="livre">
                    Livre
                </label>

                <input
                    type="text"
                    name="livre"
                    id="livre"
                    placeholder="Ex : To Love Ru"
                    required>

            </div>

            <div class="form-row">

                <label for="slug">
                    Slug
                </label>

                <input
                    type="text"
                    name="slug"
                    id="slug"
                    placeholder="Ex : to-love-ru"
                    required>

            </div>

            <div class="form-row">

                <label for="numero">
                    Numéro
                </label>

                <input
                    type="number"
                    name="numero"
                    id="numero"
                    min="1"
                    placeholder="Ex : 1"
                    required>

            </div>

            <div class="form-row">

                <label for="image">
                    Image
                </label>

                <input
                    type="file"
                    name="image"
                    id="image"
                    accept=".jpg,.jpeg,.png,.webp"
                    required>

            </div>

            <div class="form-actions">

                <button
                    type="submit"
                    class="btn-primary">
                    Ajouter
                </button>

            </div>

        </form>

    </section>

</section>