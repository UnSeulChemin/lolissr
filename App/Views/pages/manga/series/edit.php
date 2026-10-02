<?php

declare(strict_types=1);

use App\DTO\Common\Responses\FormViewData;
use App\DTO\Manga\Responses\MangaData;

/** @var MangaData $manga */
/** @var FormViewData $form */

$errors = $form->errors;
$old = $form->old;

$editeurValue = $old['editeur'] ?? $manga->editeur;

$statutValue = $old['statut'] ?? $manga->statut;

$jacquetteValue = $old['jacquette'] ?? ($manga->jacquette ?? '');

$livreNoteValue = $old['livre_note'] ?? ($manga->livreNote ?? '');

$commentaireValue = $old['commentaire'] ?? ($manga->commentaire ?? '');

$statutOptions = ['en_cours' => 'En cours', 'termine' => 'Terminé'];

?>

<section class="layout-container dashboard-page">

    <section class="form-page">

        <section class="form-card transition-form">

            <form
                class="form-layout"
                data-form-page="modifier"
                action="<?= e($form->formAction) ?>"
                method="post"
            >

                <?= csrf_field() ?>

                <div class="form-group u-stack">

                    <label
                        class="form-label u-text-center u-bold"
                        for="jacquette"
                    >

                        Note jacquette

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
                                <?= (string) $statutValue === $value ? 'selected' : '' ?>
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
                        for="commentaire"
                    >

                        Commentaire

                    </label>

                    <textarea
                        class="form-textarea"
                        name="commentaire"
                        id="commentaire"
                        rows="5"
                        maxlength="1000"
                        placeholder="Ex : défaut en haut de la jacquette"
                    ><?= e($commentaireValue) ?></textarea>

                    <?php if (isset($errors['commentaire']) && $errors['commentaire'] !== ''): ?>

                        <p class="form-error u-semibold">

                            <?= e($errors['commentaire']) ?>

                        </p>

                    <?php endif; ?>

                </div>

                <div class="form-group u-stack">

                    <label
                        class="form-label u-text-center u-bold"
                        for="note-total"
                    >

                        Note totale actuelle

                    </label>

                    <input
                        class="form-input u-text-center u-w-full"
                        type="text"
                        id="note-total"
                        value="<?= e($manga->noteLabel) ?>"
                        readonly
                    >

                </div>

                <div class="form-actions u-stack u-items-center">

                    <button
                        type="submit"
                        class="form-submit u-inline-center u-pointer u-semibold"
                    >

                        Enregistrer

                    </button>

                    <a
                        class="
                            form-submit
                            form-submit-secondary
                         u-inline-center u-pointer u-semibold"
                        href="<?= e($form->cancelUrl) ?>"
                    >

                        Annuler

                    </a>

                </div>

            </form>

        </section>

    </section>

</section>