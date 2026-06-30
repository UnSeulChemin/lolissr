<?php

declare(strict_types=1);

use Framework\Support\Session;

$errors = Session::pull('errors', []);

$old = Session::pull('old', []);

$baseUri = rtrim(
    (string) ($baseUri ?? ''),
    '/',
) . '/';

$formAction = $baseUri . 'figurine/ajouter';

$returnUrl = $baseUri . 'figurine';

?>

<section class="layout-container dashboard-page">

    <section class="form-page">

        <section class="form-card transition-form">

            <form
                class="form-layout"
                data-form-page="ajouter"
                action="<?= e($formAction) ?>"
                method="post"
                enctype="multipart/form-data"
            >

                <?= csrf_field() ?>

                <div class="form-group">

                    <label
                        class="form-label"
                        for="waifu"
                    >
                        Waifu
                    </label>

                    <input
                        class="form-input"
                        type="text"
                        name="waifu"
                        id="waifu"
                        placeholder="Ex : Asuna"
                        value="<?= e((string) ($old['waifu'] ?? '')) ?>"
                        autofocus
                        required
                    >

                    <?php if (isset($errors['waifu']) && $errors['waifu'] !== ''): ?>

                        <p class="form-error">
                            <?= e((string) $errors['waifu']) ?>
                        </p>

                    <?php endif; ?>

                </div>


                <div class="form-group">

                    <label
                        class="form-label"
                        for="slug"
                    >
                        Slug
                    </label>

                    <input
                        class="form-input"
                        type="text"
                        name="slug"
                        id="slug"
                        placeholder="Ex : asuna"
                        value="<?= e((string) ($old['slug'] ?? '')) ?>"
                        required
                    >

                    <?php if (isset($errors['slug']) && $errors['slug'] !== ''): ?>

                        <p class="form-error">
                            <?= e((string) $errors['slug']) ?>
                        </p>

                    <?php endif; ?>

                </div>


                <div class="form-group">

                    <label
                        class="form-label"
                        for="numero"
                    >
                        Numéro
                    </label>

                    <input
                        class="form-input"
                        type="number"
                        name="numero"
                        id="numero"
                        min="1"
                        placeholder="Ex : 1"
                        value="<?= e((string) ($old['numero'] ?? '')) ?>"
                        required
                    >

                    <?php if (isset($errors['numero']) && $errors['numero'] !== ''): ?>

                        <p class="form-error">
                            <?= e((string) $errors['numero']) ?>
                        </p>

                    <?php endif; ?>

                </div>


                <div class="form-group">

                    <label
                        class="form-label"
                        for="company"
                    >
                        Company
                    </label>

                    <input
                        class="form-input"
                        type="text"
                        name="company"
                        id="company"
                        placeholder="Ex : Good Smile Company"
                        value="<?= e((string) ($old['company'] ?? '')) ?>"
                        maxlength="100"
                        required
                    >

                    <?php if (isset($errors['company']) && $errors['company'] !== ''): ?>

                        <p class="form-error">
                            <?= e((string) $errors['company']) ?>
                        </p>

                    <?php endif; ?>

                </div>


                <div class="form-group">

                    <label
                        class="form-label"
                        for="image"
                    >
                        Image
                    </label>

                    <label
                        class="form-upload"
                        for="image"
                    >

                        <input
                            class="form-file"
                            type="file"
                            name="image"
                            id="image"
                            accept=".jpg,.jpeg,.png,.webp"
                            required
                        >

                        <span class="form-upload-text">
                            Choisir une image
                        </span>

                    </label>

                    <?php if (isset($errors['image']) && $errors['image'] !== ''): ?>

                        <p class="form-error">
                            <?= e((string) $errors['image']) ?>
                        </p>

                    <?php endif; ?>

                </div>


                <div class="form-group">

                    <label
                        class="form-label"
                        for="commentaire"
                    >
                        Commentaire
                    </label>

                    <textarea
                        class="form-textarea"
                        name="commentaire"
                        id="commentaire"
                        rows="4"
                        maxlength="1000"
                        placeholder="Ex : Très belle figurine, édition limitée..."
                    ><?= e((string) ($old['commentaire'] ?? '')) ?></textarea>

                    <?php if (isset($errors['commentaire']) && $errors['commentaire'] !== ''): ?>

                        <p class="form-error">
                            <?= e((string) $errors['commentaire']) ?>
                        </p>

                    <?php endif; ?>

                </div>


                <div class="form-actions">

                    <button
                        type="submit"
                        class="form-submit"
                    >
                        Ajouter
                    </button>

                    <a
                        class="
                            form-submit
                            form-submit-secondary
                        "
                        href="<?= e($returnUrl) ?>"
                    >
                        Retour
                    </a>

                </div>

            </form>

        </section>

    </section>

</section>

<?php

Session::forget([
    'errors',
    'old',
]);