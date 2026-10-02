<?php

declare(strict_types=1);

use App\DTO\Common\Responses\FormViewData;

/** @var FormViewData $form */

$errors = $form->errors;
$old = $form->old;

$artbookValue = $old['artbook'] ?? '';
$typeSourceValue = $old['type_source'] ?? 'auteur';
$sourceValue = $old['source'] ?? '';
$slugValue = $old['slug'] ?? '';
$companyValue = $old['company'] ?? '';
$numeroValue = $old['numero'] ?? '';
$releaseDateValue = $old['release_date'] ?? '';
$commentaireValue = $old['commentaire'] ?? '';

$typeSourceOptions = [
    'auteur' => 'Auteur',
    'serie' => 'Série',
];

$sourceLabel =
    $typeSourceValue === 'serie'
        ? 'Série'
        : 'Auteur';

$sourcePlaceholder =
    $typeSourceValue === 'serie'
        ? 'Ex : To Love-Ru'
        : 'Ex : Carnelian';

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
                        for="artbook"
                    >

                        Artbook

                    </label>

                    <input
                        class="form-input u-text-center u-w-full"
                        type="text"
                        name="artbook"
                        id="artbook"
                        placeholder="Ex : Carnelian Art Works"
                        value="<?= e($artbookValue) ?>"
                        maxlength="150"
                        autofocus
                        required
                    >

                    <?php if (
                        isset($errors['artbook'])
                        && $errors['artbook'] !== ''
                    ): ?>

                        <p class="form-error u-semibold">

                            <?= e($errors['artbook']) ?>

                        </p>

                    <?php endif; ?>

                </div>

                <div class="form-group u-stack">

                    <label
                        class="form-label u-text-center u-bold"
                        for="type_source"
                    >

                        Source

                    </label>

                    <select
                        class="
                            form-input
                            form-select
                         u-text-center u-w-full"
                        name="type_source"
                        id="type_source"
                        required
                    >

                        <?php foreach ($typeSourceOptions as $value => $label): ?>

                            <option
                                value="<?= e($value) ?>"
                                <?= $typeSourceValue === $value
                                    ? 'selected'
                                    : '' ?>
                            >

                                <?= e($label) ?>

                            </option>

                        <?php endforeach; ?>

                    </select>

                    <?php if (
                        isset($errors['type_source'])
                        && $errors['type_source'] !== ''
                    ): ?>

                        <p class="form-error u-semibold">

                            <?= e($errors['type_source']) ?>

                        </p>

                    <?php endif; ?>

                </div>

                <div class="form-group u-stack">

                    <label
                        class="form-label u-text-center u-bold"
                        for="source"
                    >

                        <?= e($sourceLabel) ?>

                    </label>

                    <input
                        class="form-input u-text-center u-w-full"
                        type="text"
                        name="source"
                        id="source"
                        placeholder="<?= e($sourcePlaceholder) ?>"
                        value="<?= e($sourceValue) ?>"
                        maxlength="100"
                        data-slug-source
                        required
                    >

                    <?php if (
                        isset($errors['source'])
                        && $errors['source'] !== ''
                    ): ?>

                        <p class="form-error u-semibold">

                            <?= e($errors['source']) ?>

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
                        placeholder="Ex : carnelian"
                        value="<?= e($slugValue) ?>"
                        maxlength="150"
                        data-slug-target
                        required
                    >

                    <?php if (
                        isset($errors['slug'])
                        && $errors['slug'] !== ''
                    ): ?>

                        <p class="form-error u-semibold">

                            <?= e($errors['slug']) ?>

                        </p>

                    <?php endif; ?>

                </div>

                <div class="form-group u-stack">

                    <label
                        class="form-label u-text-center u-bold"
                        for="company"
                    >

                        Entreprise

                    </label>

                    <input
                        class="form-input u-text-center u-w-full"
                        type="text"
                        name="company"
                        id="company"
                        placeholder="Ex : Kadokawa"
                        value="<?= e($companyValue) ?>"
                        maxlength="100"
                        required
                    >

                    <?php if (
                        isset($errors['company'])
                        && $errors['company'] !== ''
                    ): ?>

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
                        max="999"
                        placeholder="Ex : 1"
                        value="<?= e($numeroValue) ?>"
                        required
                    >

                    <?php if (
                        isset($errors['numero'])
                        && $errors['numero'] !== ''
                    ): ?>

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

                    <?php if (
                        isset($errors['release_date'])
                        && $errors['release_date'] !== ''
                    ): ?>

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

                    <?php if (
                        isset($errors['image'])
                        && $errors['image'] !== ''
                    ): ?>

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
                        maxlength="255"
                        placeholder="Ex : Très bel artbook, édition limitée..."
                    ><?= e($commentaireValue) ?></textarea>

                    <?php if (
                        isset($errors['commentaire'])
                        && $errors['commentaire'] !== ''
                    ): ?>

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