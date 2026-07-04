<?php

declare(strict_types=1);

use App\DTO\Common\Responses\FormViewData;

/** @var FormViewData $form */

if (! isset($peluche))
{
    throw new RuntimeException(
        'Peluche manquante dans la vue.',
    );
}

$companyValue =
    $form->old['company']
    ?? ($peluche->company ?? '');

$commentaireValue =
    $form->old['commentaire']
    ?? ($peluche->commentaire ?? '');

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
                        for="company"
                    >

                        Company

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

                    <?php if (! empty($form->errors['company'])): ?>

                        <p class="form-error">

                            <?= e($form->errors['company']) ?>

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
                        maxlength="1000"
                    ><?= e($commentaireValue) ?></textarea>

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