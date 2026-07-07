<?php

declare(strict_types=1);

use App\DTO\Common\Responses\FormViewData;
use App\DTO\Manga\Responses\ArtbookData;

/** @var ArtbookData $artbook */
/** @var FormViewData $form */

$errors = $form->errors;
$old = $form->old;

$artbookValue = $old['artbook'] ?? $artbook->artbook;
$auteurValue = $old['auteur'] ?? ($artbook->auteur ?? '');
$serieValue = $old['serie'] ?? ($artbook->serie ?? '');
$companyValue = $old['company'] ?? $artbook->company;
$releaseDateValue = $old['release_date'] ?? ($artbook->releaseDate ?? '');
$commentaireValue = $old['commentaire'] ?? ($artbook->commentaire ?? '');

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
                        id="artbook"
                        name="artbook"
                        value="<?= e($artbookValue) ?>"
                        maxlength="150"
                        required
                    >

                    <?php if (isset($errors['artbook']) && $errors['artbook'] !== ''): ?>

                        <p class="form-error">
                            <?= e($errors['artbook']) ?>
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
                        id="auteur"
                        name="auteur"
                        value="<?= e($auteurValue) ?>"
                        maxlength="100"
                    >

                    <?php if (isset($errors['auteur']) && $errors['auteur'] !== ''): ?>

                        <p class="form-error">
                            <?= e($errors['auteur']) ?>
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
                        id="serie"
                        name="serie"
                        value="<?= e($serieValue) ?>"
                        maxlength="100"
                    >

                    <?php if (isset($errors['serie']) && $errors['serie'] !== ''): ?>

                        <p class="form-error">
                            <?= e($errors['serie']) ?>
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
                        id="company"
                        name="company"
                        value="<?= e($companyValue) ?>"
                        maxlength="100"
                        required
                    >

                    <?php if (isset($errors['company']) && $errors['company'] !== ''): ?>

                        <p class="form-error">
                            <?= e($errors['company']) ?>
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
                        id="release_date"
                        name="release_date"
                        placeholder="JJ/MM/AAAA"
                        value="<?= e($releaseDateValue) ?>"
                        maxlength="10"
                    >

                    <?php if (isset($errors['release_date']) && $errors['release_date'] !== ''): ?>

                        <p class="form-error">
                            <?= e($errors['release_date']) ?>
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
                        rows="5"
                        maxlength="255"
                    ><?= e($commentaireValue) ?></textarea>

                    <?php if (isset($errors['commentaire']) && $errors['commentaire'] !== ''): ?>

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
                        Enregistrer
                    </button>

                    <a
                        class="
                            form-submit
                            form-submit-secondary
                        "
                        href="<?= e($form->cancelUrl) ?>"
                    >
                        Annuler
                    </a>

                </div>

            </form>

        </section>

    </section>

</section>