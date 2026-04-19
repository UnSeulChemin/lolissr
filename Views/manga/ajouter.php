<?php

use App\Core\Support\Session;

$errors = Session::get('errors', []);
$old = Session::get('old', []);

?>

<section class="layout-container dashboard-page">

    <section class="form-page animate-fade-up">

        <section class="form-card">

            <form
                class="form-layout"
                action="<?= $basePath; ?>manga/ajouter"
                method="post"
                enctype="multipart/form-data">

                <div class="form-group">

                    <label class="form-label" for="livre">
                        Livre
                    </label>

                    <input
                        class="form-input"
                        type="text"
                        name="livre"
                        id="livre"
                        placeholder="Ex : To Love Ru"
                        value="<?= htmlspecialchars($old['livre'] ?? '') ?>"
                        autofocus
                        required>

                    <?php if (!empty($errors['livre'])): ?>
                        <p class="form-error">
                            <?= htmlspecialchars($errors['livre']) ?>
                        </p>
                    <?php endif; ?>

                </div>

                <div class="form-group">

                    <label class="form-label" for="slug">
                        Slug
                    </label>

                    <input
                        class="form-input"
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

                <div class="form-group">

                    <label class="form-label" for="numero">
                        Numéro
                    </label>

                    <input
                        class="form-input"
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

                <div class="form-group">

                    <label class="form-label" for="image">
                        Image
                    </label>

                    <label class="form-upload" for="image">

                        <input
                            class="form-file"
                            type="file"
                            name="image"
                            id="image"
                            accept=".jpg,.jpeg,.png,.webp"
                            required>

                        <span class="form-upload-text">
                            Choisir une image
                        </span>

                    </label>

                    <?php if (!empty($errors['image'])): ?>
                        <p class="form-error">
                            <?= htmlspecialchars($errors['image']) ?>
                        </p>
                    <?php endif; ?>

                </div>

                <div class="form-group">

                    <label class="form-label" for="commentaire">
                        Commentaire
                    </label>

                    <textarea
                        class="form-textarea"
                        name="commentaire"
                        id="commentaire"
                        rows="4"
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
                        class="form-submit">
                        Ajouter
                    </button>

                    <a
                        class="form-submit form-submit-secondary"
                        href="<?= $basePath; ?>manga">
                        Retour
                    </a>

                </div>

            </form>

        </section>

    </section>

</section>

<?php Session::forget(['errors', 'old']); ?>