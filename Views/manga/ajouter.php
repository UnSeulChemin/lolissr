<?php

use App\Core\Session;

$errors = Session::get('errors', []);
$old = Session::get('old', []);
$error = Session::pull('error');
$success = Session::pull('success');
?>

<section class="layout-container">
    <section class="manga-form-page">

        <section class="manga-form-card">
            <?php if (!empty($error)): ?>
                <div class="alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <form class="manga-form" action="<?= $basePath; ?>manga/ajouter" method="post" enctype="multipart/form-data">
                <div class="manga-form-group">
                    <label class="manga-form-label" for="livre">Livre</label>
                    <input class="manga-form-input" type="text" name="livre" id="livre" placeholder="Ex : To Love Ru" value="<?= htmlspecialchars($old['livre'] ?? '') ?>" required>
                    <?php if (!empty($errors['livre'])): ?>
                        <p class="form-error"><?= htmlspecialchars($errors['livre']) ?></p>
                    <?php endif; ?>
                </div>

                <div class="manga-form-group">
                    <label class="manga-form-label" for="slug">Slug</label>
                    <input class="manga-form-input" type="text" name="slug" id="slug" placeholder="Ex : to-love-ru" value="<?= htmlspecialchars($old['slug'] ?? '') ?>" required>
                    <?php if (!empty($errors['slug'])): ?>
                        <p class="form-error"><?= htmlspecialchars($errors['slug']) ?></p>
                    <?php endif; ?>
                </div>

                <div class="manga-form-group">
                    <label class="manga-form-label" for="numero">Numéro</label>
                    <input class="manga-form-input" type="number" name="numero" id="numero" min="1" placeholder="Ex : 1" value="<?= htmlspecialchars($old['numero'] ?? '') ?>" required>
                    <?php if (!empty($errors['numero'])): ?>
                        <p class="form-error"><?= htmlspecialchars($errors['numero']) ?></p>
                    <?php endif; ?>
                </div>

                <div class="manga-form-group">
                    <label class="manga-form-label" for="image">Image</label>

                    <label class="manga-form-upload" for="image">
                        <input class="manga-form-file" type="file" name="image" id="image" accept=".jpg,.jpeg,.png,.webp" required>
                        <span class="manga-upload-text">Choisir une image</span>
                    </label>

                    <?php if (!empty($errors['image'])): ?>
                        <p class="form-error"><?= htmlspecialchars($errors['image']) ?></p>
                    <?php endif; ?>
                </div>

                <div class="manga-form-group">
                    <label class="manga-form-label" for="commentaire">Commentaire</label>
                    <textarea class="manga-form-textarea" name="commentaire" id="commentaire" rows="4" maxlength="1000" placeholder="Ex : défaut en haut de la jacquette"><?= htmlspecialchars($old['commentaire'] ?? '') ?></textarea>
                    <?php if (!empty($errors['commentaire'])): ?>
                        <p class="form-error"><?= htmlspecialchars($errors['commentaire']) ?></p>
                    <?php endif; ?>
                </div>

                <div class="manga-form-actions">
                    <button type="submit" class="manga-form-submit">Ajouter</button>
                </div>
            </form>
        </section>
    </section>
</section>

<?php Session::forget(['errors', 'old']); ?>

<script>
const livreInput = document.getElementById('livre');
const slugInput = document.getElementById('slug');
const imageInput = document.getElementById('image');
const uploadText = document.querySelector('.manga-upload-text');

livreInput.addEventListener('input', function () {
    const slug = this.value
        .toLowerCase()
        .trim()
        .replace(/[^a-z0-9\s-]/g, '')
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-')
        .replace(/^-|-$/g, '');

    slugInput.value = slug;
});

imageInput.addEventListener('change', function () {
    if (this.files.length > 0) {
        uploadText.textContent = this.files[0].name;
    } else {
        uploadText.textContent = 'Choisir une image';
    }
});
</script>