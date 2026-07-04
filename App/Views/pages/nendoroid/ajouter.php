<?php

declare(strict_types=1);

use App\DTO\Common\Responses\FormViewData;

/** @var FormViewData $form */

?>

<section class="layout-container dashboard-page">

    <section class="form-page">

        <section class="form-card transition-form">

            <form
                class="form-layout"
                data-form-page="ajouter"
                action="<?= e($form->formAction) ?>"
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
                        value="<?= e((string) ($form->old['waifu'] ?? '')) ?>"
                        autofocus
                        required
                    >

                    <?php if (! empty($form->errors['waifu'])): ?>

                        <p class="form-error">

                            <?= e($form->errors['waifu']) ?>

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
                        value="<?= e((string) ($form->old['slug'] ?? '')) ?>"
                        required
                    >

                    <?php if (! empty($form->errors['slug'])): ?>

                        <p class="form-error">

                            <?= e($form->errors['slug']) ?>

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
                        value="<?= e((string) ($form->old['numero'] ?? '')) ?>"
                        required
                    >

                    <?php if (! empty($form->errors['numero'])): ?>

                        <p class="form-error">

                            <?= e($form->errors['numero']) ?>

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
                        value="<?= e((string) ($form->old['company'] ?? '')) ?>"
                        maxlength="100"
                        required
                    >

                    <?php if (! empty($form->errors['company'])): ?>

                        <p class="form-error">

                            <?= e($form->errors['company']) ?>

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

                    <?php if (! empty($form->errors['image'])): ?>

                        <p class="form-error">

                            <?= e($form->errors['image']) ?>

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
                        placeholder="Ex : Très belle Nendoroid, édition limitée..."
                    ><?= e((string) ($form->old['commentaire'] ?? '')) ?></textarea>

                    <?php if (! empty($form->errors['commentaire'])): ?>

                        <p class="form-error">

                            <?= e($form->errors['commentaire']) ?>

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
                        href="<?= e($form->cancelUrl) ?>"
                    >

                        Retour

                    </a>

                </div>

            </form>

        </section>

    </section>

</section>