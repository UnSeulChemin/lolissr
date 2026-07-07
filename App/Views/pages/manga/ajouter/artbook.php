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
                        value="<?= e($artbookValue) ?>"
                        autofocus
                        required
                    >

                    <?php if (
                        isset($errors['artbook'])
                        && $errors['artbook'] !== ''
                    ): ?>

                        <p class="form-error">

                            <?= e($errors['artbook']) ?>

                        </p>

                    <?php endif; ?>

                </div>

                <div class="form-group">

                    <label
                        class="form-label"
                        for="type_source"
                    >

                        Type

                    </label>

                    <select
                        class="
                            form-input
                            form-select
                        "
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

                        <p class="form-error">

                            <?= e($errors['type_source']) ?>

                        </p>

                    <?php endif; ?>

                </div>

                <div class="form-group">

                    <label
                        class="form-label"
                        for="source"
                    >

                        Auteur / Série

                    </label>

                    <input
                        class="form-input"
                        type="text"
                        name="source"
                        id="source"
                        placeholder="Ex : Carnelian ou To Love-Ru"
                        value="<?= e($sourceValue) ?>"
                        maxlength="100"
                        data-slug-source
                        required
                    >

                    <?php if (
                        isset($errors['source'])
                        && $errors['source'] !== ''
                    ): ?>

                        <p class="form-error">

                            <?= e($errors['source']) ?>

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
                        placeholder="Ex : carnelian"
                        value="<?= e($slugValue) ?>"
                        data-slug-target
                        required
                    >

                    <?php if (
                        isset($errors['slug'])
                        && $errors['slug'] !== ''
                    ): ?>

                        <p class="form-error">

                            <?= e($errors['slug']) ?>

                        </p>

                    <?php endif; ?>

                </div>

                <div class="form-group">

                    <label
                        class="form-label"
                        for="company"
                    >

                        Entreprise

                    </label>

                    <input
                        class="form-input"
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

                        <p class="form-error">

                            <?= e($errors['company']) ?>

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
                        value="<?= e($numeroValue) ?>"
                        required
                    >

                    <?php if (
                        isset($errors['numero'])
                        && $errors['numero'] !== ''
                    ): ?>

                        <p class="form-error">

                            <?= e($errors['numero']) ?>

                        </p>

                    <?php endif; ?>

                </div>

                <div class="form-group">

                    <label
                        class="form-label"
                        for="release_date"
                    >

                        Date de sortie

                    </label>

                    <input
                        class="form-input"
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

                        <p class="form-error">

                            <?= e($errors['release_date']) ?>

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

                            <?= e($errors['image']) ?>

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
                        maxlength="255"
                        placeholder="Ex : Très bel artbook, édition limitée..."
                    ><?= e($commentaireValue) ?></textarea>

                    <?php if (
                        isset($errors['commentaire'])
                        && $errors['commentaire'] !== ''
                    ): ?>

                        <p class="form-error">

                            <?= e($errors['commentaire']) ?>

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