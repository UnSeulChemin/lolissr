<section class="section-content">

    <section class="form-box">

        <h1 class="form-title">
            Ajouter un manga
        </h1>

        <?php if (!empty($_SESSION['error'])): ?>
            <div class="alert-error">
                <?= htmlspecialchars($_SESSION['error']) ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

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
                    value="<?= htmlspecialchars($_POST['livre'] ?? '') ?>"
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
                    value="<?= htmlspecialchars($_POST['slug'] ?? '') ?>"
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
                    value="<?= htmlspecialchars($_POST['numero'] ?? '') ?>"
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

            <div class="form-row">

                <label for="commentaire">
                    Commentaire
                </label>

                <textarea
                    name="commentaire"
                    id="commentaire"
                    rows="3"
                    maxlength="255"
                    placeholder="Ex : défaut en haut de la jacquette"><?= htmlspecialchars($_POST['commentaire'] ?? '') ?></textarea>

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

<script>
document.getElementById('livre').addEventListener('input', function () {
    let slug = this.value
        .toLowerCase()
        .trim()
        .replace(/[^a-z0-9\s-]/g, '')
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-');

    document.getElementById('slug').value = slug;
});
</script>