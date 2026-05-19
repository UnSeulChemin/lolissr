<?php

declare(strict_types=1);

use App\Core\Support\Session;

$errors = Session::get('errors', []);
$old = Session::get('old', []);

$basePath = rtrim($basePath, '/') . '/';

$statutValue = $old['statut'] ?? 'en_cours';

$statutOptions = [
    'en_cours' => 'En cours',
    'termine' => 'Terminé',
];

$formAction = $basePath . 'manga/ajouter';

$returnUrl = $basePath . 'manga';

?>

<section class="layout-container dashboard-page">

    <section class="form-page animate-fade-up">

        <section class="form-card">

            <form
                class="form-layout"
                data-form-page="ajouter"
                action="<?= e($formAction) ?>"
                method="post"
                enctype="multipart/form-data">

                <?= csrf_field() ?>

                <div class="form-group">

                    <label
                        class="form-label"
                        for="livre">

                        Livre

                    </label>

                    <input
                        class="form-input"
                        type="text"
                        name="livre"
                        id="livre"
                        placeholder="Ex : To Love Ru"
                        value="<?= e($old['livre'] ?? '') ?>"
                        autofocus
                        required>

                    <?php if (!empty($errors['livre'])): ?>

                        <p class="form-error">

                            <?= e($errors['livre']) ?>

                        </p>

                    <?php endif; ?>

                </div>

                <div class="form-group">

                    <label
                        class="form-label"
                        for="slug">

                        Slug

                    </label>

                    <input
                        class="form-input"
                        type="text"
                        name="slug"
                        id="slug"
                        placeholder="Ex : to-love-ru"
                        value="<?= e($old['slug'] ?? '') ?>"
                        required>

                    <?php if (!empty($errors['slug'])): ?>

                        <p class="form-error">

                            <?= e($errors['slug']) ?>

                        </p>

                    <?php endif; ?>

                </div>

                <div class="form-group">

                    <label
                        class="form-label"
                        for="editeur">

                        Éditeur

                    </label>

                    <input
                        class="form-input"
                        type="text"
                        name="editeur"
                        id="editeur"
                        placeholder="Ex : Delcourt/Tonkam"
                        value="<?= e($old['editeur'] ?? '') ?>"
                        maxlength="100"
                        required>

                    <?php if (!empty($errors['editeur'])): ?>

                        <p class="form-error">

                            <?= e($errors['editeur']) ?>

                        </p>

                    <?php endif; ?>

                </div>

                <div class="form-group">

                    <label
                        class="form-label"
                        for="statut">

                        Statut

                    </label>

                    <select
                        class="form-input form-select"
                        name="statut"
                        id="statut"
                        required>

                        <?php foreach ($statutOptions as $value => $label): ?>

                            <option
                                value="<?= e($value) ?>"
                                <?= $statutValue === $value
                                    ? 'selected'
                                    : '' ?>>

                                <?= e($label) ?>

                            </option>

                        <?php endforeach; ?>

                    </select>

                    <?php if (!empty($errors['statut'])): ?>

                        <p class="form-error">

                            <?= e($errors['statut']) ?>

                        </p>

                    <?php endif; ?>

                </div>

                <div class="form-group">

                    <label
                        class="form-label"
                        for="numero">

                        Numéro

                    </label>

                    <input
                        class="form-input"
                        type="number"
                        name="numero"
                        id="numero"
                        min="1"
                        placeholder="Ex : 1"
                        value="<?= e($old['numero'] ?? '') ?>"
                        required>

                    <?php if (!empty($errors['numero'])): ?>

                        <p class="form-error">

                            <?= e($errors['numero']) ?>

                        </p>

                    <?php endif; ?>

                </div>

                <div class="form-group">

                    <label
                        class="form-label"
                        for="image">

                        Image

                    </label>

                    <label
                        class="form-upload"
                        for="image">

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

                            <?= e($errors['image']) ?>

                        </p>

                    <?php endif; ?>

                </div>

                <div class="form-group">

                    <label
                        class="form-label"
                        for="commentaire">

                        Commentaire

                    </label>

                    <textarea
                        class="form-textarea"
                        name="commentaire"
                        id="commentaire"
                        rows="4"
                        maxlength="1000"
                        placeholder="Ex : défaut en haut de la jacquette"><?= e($old['commentaire'] ?? '') ?></textarea>

                    <?php if (!empty($errors['commentaire'])): ?>

                        <p class="form-error">

                            <?= e($errors['commentaire']) ?>

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
                        href="<?= e($returnUrl) ?>">

                        Retour

                    </a>

                </div>

            </form>

        </section>

    </section>

</section>

<?php Session::forget(['errors', 'old']); ?>