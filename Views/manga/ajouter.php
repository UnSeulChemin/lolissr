<?php

use App\Core\Session;

$errors = Session::get('errors', []);
$old = Session::get('old', []);
$error = Session::pull('error');
$success = Session::pull('success');
?>

<section class="section-content">

    <section class="form-box">

        <h1 class="form-title">
            Ajouter un manga
        </h1>

        <?php if (!empty($error)): ?>
            <div class="alert-error">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert-success">
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <form class="form-add"
              action="<?= $basePath; ?>manga/ajouter"
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
                    value="<?= htmlspecialchars($old['livre'] ?? '') ?>"
                    required>

                <?php if (!empty($errors['livre'])): ?>
                    <p class="form-error">
                        <?= htmlspecialchars($errors['livre']) ?>
                    </p>
                <?php endif; ?>

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
                    value="<?= htmlspecialchars($old['slug'] ?? '') ?>"
                    required>

                <?php if (!empty($errors['slug'])): ?>
                    <p class="form-error">
                        <?= htmlspecialchars($errors['slug']) ?>
                    </p>
                <?php endif; ?>

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
                    value="<?= htmlspecialchars($old['numero'] ?? '') ?>"
                    required>

                <?php if (!empty($errors['numero'])): ?>
                    <p class="form-error">
                        <?= htmlspecialchars($errors['numero']) ?>
                    </p>
                <?php endif; ?>

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
                    maxlength="1000"
                    placeholder="Ex : défaut en haut de la jacquette"><?= htmlspecialchars($old['commentaire'] ?? '') ?></textarea>

                <?php if (!empty($errors['commentaire'])): ?>
                    <p class="form-error">
                        <?= htmlspecialchars($errors['commentaire']) ?>
                    </p>
                <?php endif; ?>

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

<?php Session::forget(['errors', 'old']); ?>

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