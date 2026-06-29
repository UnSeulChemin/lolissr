<?php

declare(strict_types=1);

use Framework\Support\Session;

$errors =
    Session::pull('errors', []);

$old =
    Session::pull('old', []);

$baseUri =
    rtrim(
        (string) ($baseUri ?? ''),
        '/',
    ) . '/';

$formAction =
    $baseUri
    . 'manga/ajouter/artbook';

$returnUrl =
    $baseUri
    . 'manga';

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
                        for="artbook"
                    >

                        Artbook

                    </label>

                    <input
                        class="form-input"
                        type="text"
                        name="artbook"
                        id="artbook"
                        placeholder="Ex : Carnelian Art Works"
                        value="<?= e((string) ($old['artbook'] ?? '')) ?>"
                        autofocus
                        required
                    >

                    <?php if (
                        isset($errors['artbook'])
                        && $errors['artbook'] !== ''
                    ): ?>

                        <p class="form-error">

                            <?= e((string) $errors['artbook']) ?>

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
                        placeholder="Ex : carnelian-art-works"
                        value="<?= e((string) ($old['slug'] ?? '')) ?>"
                        required
                    >

                    <?php if (
                        isset($errors['slug'])
                        && $errors['slug'] !== ''
                    ): ?>

                        <p class="form-error">

                            <?= e((string) $errors['slug']) ?>

                        </p>

                    <?php endif; ?>

                </div>


                <div class="form-group">

                    <label
                        class="form-label"
                        for="auteur"
                    >

                        Auteur

                    </label>

                    <input
                        class="form-input"
                        type="text"
                        name="auteur"
                        id="auteur"
                        placeholder="Ex : Carnelian"
                        value="<?= e((string) ($old['auteur'] ?? '')) ?>"
                        maxlength="100"
                    >

                    <?php if (
                        isset($errors['auteur'])
                        && $errors['auteur'] !== ''
                    ): ?>

                        <p class="form-error">

                            <?= e((string) $errors['auteur']) ?>

                        </p>

                    <?php endif; ?>

                </div>


                <div class="form-group">

                    <label
                        class="form-label"
                        for="serie"
                    >

                        Série

                    </label>

                    <input
                        class="form-input"
                        type="text"
                        name="serie"
                        id="serie"
                        placeholder="Ex : Original"
                        value="<?= e((string) ($old['serie'] ?? '')) ?>"
                        maxlength="100"
                    >

                    <?php if (
                        isset($errors['serie'])
                        && $errors['serie'] !== ''
                    ): ?>

                        <p class="form-error">

                            <?= e((string) $errors['serie']) ?>

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

                    <?php if (
                        isset($errors['numero'])
                        && $errors['numero'] !== ''
                    ): ?>

                        <p class="form-error">

                            <?= e((string) $errors['numero']) ?>

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

                    <?php if (
                        isset($errors['image'])
                        && $errors['image'] !== ''
                    ): ?>

                        <p class="form-error">

                            <?= e((string) $errors['image']) ?>

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