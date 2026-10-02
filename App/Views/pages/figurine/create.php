<?php

declare(strict_types=1);

use App\DTO\Common\Responses\FormViewData;

/** @var FormViewData $form */

$errors = $form->errors;
$old = $form->old;

$waifuValue = $old['waifu'] ?? '';
$originValue = $old['origin'] ?? '';
$slugValue = $old['slug'] ?? '';
$scaleValue = $old['scale'] ?? '';
$heightValue = $old['height_cm'] ?? '';
$companyValue = $old['company'] ?? '';
$numeroValue = $old['numero'] ?? '';
$releaseDateValue = $old['release_date'] ?? '';
$commentaireValue = $old['commentaire'] ?? '';

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

                <div class="form-group u-stack">

                    <label
                        class="form-label u-text-center u-bold"
                        for="waifu"
                    >
                        Waifu
                    </label>

                    <input
                        class="form-input u-text-center u-w-full"
                        type="text"
                        name="waifu"
                        id="waifu"
                        placeholder="Ex : Asuna"
                        value="<?= e($waifuValue) ?>"
                        autofocus
                        required
                    >

                    <?php if (isset($errors['waifu']) && $errors['waifu'] !== ''): ?>

                        <p class="form-error u-semibold">
                            <?= e($errors['waifu']) ?>
                        </p>

                    <?php endif; ?>

                </div>

                <div class="form-group u-stack">

                    <label
                        class="form-label u-text-center u-bold"
                        for="origin"
                    >
                        Origin
                    </label>

                    <input
                        class="form-input u-text-center u-w-full"
                        type="text"
                        name="origin"
                        id="origin"
                        placeholder="Ex : Yosuga no Sora"
                        value="<?= e($originValue) ?>"
                        maxlength="150"
                        required
                    >

                    <?php if (isset($errors['origin']) && $errors['origin'] !== ''): ?>

                        <p class="form-error u-semibold">
                            <?= e($errors['origin']) ?>
                        </p>

                    <?php endif; ?>

                </div>

                <div class="form-group u-stack">

                    <label
                        class="form-label u-text-center u-bold"
                        for="slug"
                    >
                        Slug
                    </label>

                    <input
                        class="form-input u-text-center u-w-full"
                        type="text"
                        name="slug"
                        id="slug"
                        placeholder="Ex : asuna"
                        value="<?= e($slugValue) ?>"
                        required
                    >

                    <?php if (isset($errors['slug']) && $errors['slug'] !== ''): ?>

                        <p class="form-error u-semibold">
                            <?= e($errors['slug']) ?>
                        </p>

                    <?php endif; ?>

                </div>

                <div class="form-group u-stack">

                    <label
                        class="form-label u-text-center u-bold"
                        for="scale"
                    >
                        Échelle
                    </label>

                    <input
                        class="form-input u-text-center u-w-full"
                        type="text"
                        name="scale"
                        id="scale"
                        placeholder="Ex : 1/7"
                        value="<?= e($scaleValue) ?>"
                        maxlength="10"
                        required
                    >

                    <?php if (isset($errors['scale']) && $errors['scale'] !== ''): ?>

                        <p class="form-error u-semibold">
                            <?= e($errors['scale']) ?>
                        </p>

                    <?php endif; ?>

                </div>

                <div class="form-group u-stack">

                    <label
                        class="form-label u-text-center u-bold"
                        for="height_cm"
                    >
                        Hauteur (cm)
                    </label>

                    <input
                        class="form-input u-text-center u-w-full"
                        type="number"
                        name="height_cm"
                        id="height_cm"
                        min="0"
                        step="0.1"
                        placeholder="Ex : 24.5"
                        value="<?= e($heightValue) ?>"
                    >

                    <?php if (isset($errors['height_cm']) && $errors['height_cm'] !== ''): ?>

                        <p class="form-error u-semibold">
                            <?= e($errors['height_cm']) ?>
                        </p>

                    <?php endif; ?>

                </div>


                <div class="form-group u-stack">

                    <label
                        class="form-label u-text-center u-bold"
                        for="company"
                    >
                        Company
                    </label>

                    <input
                        class="form-input u-text-center u-w-full"
                        type="text"
                        name="company"
                        id="company"
                        placeholder="Ex : Good Smile Company"
                        value="<?= e($companyValue) ?>"
                        maxlength="100"
                        required
                    >

                    <?php if (isset($errors['company']) && $errors['company'] !== ''): ?>

                        <p class="form-error u-semibold">
                            <?= e($errors['company']) ?>
                        </p>

                    <?php endif; ?>

                </div>

                <div class="form-group u-stack">

                    <label
                        class="form-label u-text-center u-bold"
                        for="numero"
                    >
                        Numéro
                    </label>

                    <input
                        class="form-input u-text-center u-w-full"
                        type="number"
                        name="numero"
                        id="numero"
                        min="1"
                        placeholder="Ex : 1"
                        value="<?= e($numeroValue) ?>"
                        required
                    >

                    <?php if (isset($errors['numero']) && $errors['numero'] !== ''): ?>

                        <p class="form-error u-semibold">
                            <?= e($errors['numero']) ?>
                        </p>

                    <?php endif; ?>

                </div>

                <div class="form-group u-stack">

                    <label
                        class="form-label u-text-center u-bold"
                        for="release_date"
                    >
                        Date de sortie
                    </label>

                    <input
                        class="form-input u-text-center u-w-full"
                        type="text"
                        name="release_date"
                        id="release_date"
                        placeholder="JJ/MM/AAAA"
                        value="<?= e($releaseDateValue) ?>"
                        maxlength="10"
                    >

                    <?php if (isset($errors['release_date']) && $errors['release_date'] !== ''): ?>

                        <p class="form-error u-semibold">
                            <?= e($errors['release_date']) ?>
                        </p>

                    <?php endif; ?>

                </div>

                <div class="form-group u-stack">

                    <label
                        class="form-label u-text-center u-bold"
                        for="image"
                    >
                        Image
                    </label>

                    <label
                        class="form-upload u-row-center u-relative u-pointer"
                        for="image"
                    >

                        <input
                            class="form-file u-absolute u-pointer"
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

                        <p class="form-error u-semibold">
                            <?= e($errors['image']) ?>
                        </p>

                    <?php endif; ?>

                </div>


                <div class="form-group u-stack">

                    <label
                        class="form-label u-text-center u-bold"
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
                    ><?= e($commentaireValue) ?></textarea>

                    <?php if (isset($errors['commentaire']) && $errors['commentaire'] !== ''): ?>

                        <p class="form-error u-semibold">
                            <?= e($errors['commentaire']) ?>
                        </p>

                    <?php endif; ?>

                </div>


                <div class="form-actions u-stack u-items-center">

                    <button
                        type="submit"
                        class="form-submit u-inline-center u-pointer u-semibold"
                    >
                        Ajouter
                    </button>

                    <a
                        class="
                            form-submit
                            form-submit-secondary
                         u-inline-center u-pointer u-semibold"
                        href="<?= e($form->cancelUrl) ?>"
                    >
                        Retour
                    </a>

                </div>

            </form>

        </section>

    </section>

</section>
