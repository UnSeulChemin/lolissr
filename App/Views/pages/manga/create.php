<?php

declare(strict_types=1);

use App\DTO\Common\Responses\FormViewData;

/** @var FormViewData $form */
/** @var list<array{slug: string, livre: string, editeur: ?string, statut: string, next_numero: int}> $existingSeries */

$errors = $form->errors;
$old = $form->old;

$livreValue = $old['livre'] ?? '';
$slugValue = $old['slug'] ?? '';
$editeurValue = $old['editeur'] ?? '';
$numeroValue = $old['numero'] ?? '';
$commentaireValue = $old['commentaire'] ?? '';

$jacquetteValue = $old['jacquette'] ?? '';
$livreNoteValue = $old['livre_note'] ?? '';

$statutValue = $old['statut'] ?? 'en_cours';

$statutOptions = ['en_cours' => 'En cours', 'termine' => 'Terminé'];

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

                <div class="form-group manga-series-field u-stack">

                    <label
                        class="form-label u-text-center u-bold"
                        for="livre"
                    >

                        Livre

                    </label>

                    <input
                        class="form-input u-text-center u-w-full"
                        type="text"
                        name="livre"
                        id="livre"
                        data-slug-source
                        role="combobox"
                        aria-autocomplete="list"
                        aria-expanded="false"
                        aria-controls="manga-series-suggestions"
                        autocomplete="off"
                        placeholder="Ex : To Love Ru"
                        value="<?= e($livreValue) ?>"
                        autofocus
                        required
                    >

                    <datalist id="manga-existing-series">
                        <?php foreach ($existingSeries as $series): ?>
                            <option
                                value="<?= e($series['livre']) ?>"
                                label="<?= e($series['editeur'] ?? '') ?>"
                                data-slug="<?= e($series['slug']) ?>"
                                data-editeur="<?= e($series['editeur'] ?? '') ?>"
                                data-statut="<?= e($series['statut']) ?>"
                                data-numero="<?= e($series['next_numero']) ?>"
                            ></option>
                        <?php endforeach; ?>
                    </datalist>
                    <div class="manga-series-suggestions" id="manga-series-suggestions" role="listbox" aria-label="Séries de ta collection" hidden></div>

                    <?php if (isset($errors['livre']) && $errors['livre'] !== ''): ?>

                        <p class="form-error u-semibold">

                            <?= e($errors['livre']) ?>

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
                        data-slug-target
                        placeholder="Ex : to-love-ru"
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
                        for="editeur"
                    >

                        Éditeur

                    </label>

                    <input
                        class="form-input u-text-center u-w-full"
                        type="text"
                        name="editeur"
                        id="editeur"
                        placeholder="Ex : Delcourt/Tonkam"
                        value="<?= e($editeurValue) ?>"
                        maxlength="100"
                        required
                    >

                    <?php if (isset($errors['editeur']) && $errors['editeur'] !== ''): ?>

                        <p class="form-error u-semibold">

                            <?= e($errors['editeur']) ?>

                        </p>

                    <?php endif; ?>

                </div>


                <div class="form-group u-stack">

                    <label
                        class="form-label u-text-center u-bold"
                        for="statut"
                    >

                        Statut

                    </label>

                    <select
                        class="
                            form-input
                            form-select
                         u-text-center u-w-full"
                        name="statut"
                        id="statut"
                        required
                    >

                        <?php foreach ($statutOptions as $value => $label): ?>

                            <option
                                value="<?= e($value) ?>"
                                <?= $statutValue === $value
                                    ? 'selected'
                                    : '' ?>
                            >

                                <?= e($label) ?>

                            </option>

                        <?php endforeach; ?>

                    </select>

                    <?php if (isset($errors['statut']) && $errors['statut'] !== ''): ?>

                        <p class="form-error u-semibold">

                            <?= e($errors['statut']) ?>

                        </p>

                    <?php endif; ?>

                </div>


                <div class="form-group u-stack">

                    <label
                        class="form-label u-text-center u-bold"
                        for="jacquette"
                    >

                        Note jaquette

                    </label>

                    <select
                        class="
                            form-input
                            form-select
                         u-text-center u-w-full"
                        name="jacquette"
                        id="jacquette"
                    >

                        <option value="">
                            Choisir
                        </option>

                        <?php for ($i = 1; $i <= 5; $i++): ?>

                            <option
                                value="<?= $i ?>"
                                <?= (string) $jacquetteValue === (string) $i ? 'selected' : '' ?>
                            >

                                <?= $i ?>

                            </option>

                        <?php endfor; ?>

                    </select>

                    <?php if (isset($errors['jacquette']) && $errors['jacquette'] !== ''): ?>

                        <p class="form-error u-semibold">

                            <?= e($errors['jacquette']) ?>

                        </p>

                    <?php endif; ?>

                </div>

                <div class="form-group u-stack">

                    <label
                        class="form-label u-text-center u-bold"
                        for="livre_note"
                    >

                        Note livre

                    </label>

                    <select
                        class="
                            form-input
                            form-select
                         u-text-center u-w-full"
                        name="livre_note"
                        id="livre_note"
                    >

                        <option value="">
                            Choisir
                        </option>

                        <?php for ($i = 1; $i <= 5; $i++): ?>

                            <option
                                value="<?= $i ?>"
                                <?= (string) $livreNoteValue === (string) $i ? 'selected' : '' ?>
                            >

                                <?= $i ?>

                            </option>

                        <?php endfor; ?>

                    </select>

                    <?php if (isset($errors['livre_note']) && $errors['livre_note'] !== ''): ?>

                        <p class="form-error u-semibold">

                            <?= e($errors['livre_note']) ?>

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
                        placeholder="Ex : défaut en haut de la jacquette"
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
