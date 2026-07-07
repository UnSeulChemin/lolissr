<?php

declare(strict_types=1);

use App\DTO\Common\Responses\FormViewData;
use App\DTO\Manga\Responses\ArtbookData;

/** @var ArtbookData $artbook */
/** @var FormViewData $form */

$errors = $form->errors;
$old = $form->old;

$artbookValue = $old['artbook'] ?? $artbook->artbook;

$isSerie = $artbook->hasSerie;

$sourceValue = $old['source']
    ?? ($isSerie
        ? ($artbook->serie ?? '')
        : ($artbook->auteur ?? ''));

$sourceLabel = $isSerie
    ? 'Série'
    : 'Auteur';

$sourcePlaceholder = $isSerie
    ? 'Ex : To Love-Ru'
    : 'Ex : Carnelian';

$companyValue = $old['company'] ?? $artbook->company;

$releaseDateValue = $old['release_date']
    ?? ($artbook->releaseDate ?? '');

$commentaireValue = $old['commentaire']
    ?? ($artbook->commentaire ?? '');

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
                        name="artbook"
                        id="artbook"
                        value="<?= e($artbookValue) ?>"
                        maxlength="150"
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
                        for="source"
                    >

                        <?= e($sourceLabel) ?>

                    </label>

                    <input
                        class="form-input"
                        type="text"
                        name="source"
                        id="source"
                        placeholder="<?= e($sourcePlaceholder) ?>"
                        value="<?= e($sourceValue) ?>"
                        maxlength="100"
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
                        for="company"
                    >

                        Entreprise

                    </label>

                    <input
                        class="form-input"
                        type="text"
                        name="company"
                        id="company"
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