<?php

declare(strict_types=1);

use Framework\Support\Session;

if (! isset($nendoroid))
{
    throw new RuntimeException(
        'Nendoroid manquante dans la vue.',
    );
}

$errors =
    Session::pull('errors', []);

$old =
    Session::pull('old', []);

$companyValue =
    $old['company']
    ?? ($nendoroid->company ?? '');

$commentaireValue =
    $old['commentaire']
    ?? ($nendoroid->commentaire ?? '');

$formAction =
    $view->baseUri
    . 'nendoroid/waifus/'
    . rawurlencode((string) $nendoroid->slug)
    . '/modifier/'
    . $nendoroid->numero;

$cancelUrl =
    $view->baseUri
    . 'nendoroid/waifus/'
    . rawurlencode((string) $nendoroid->slug)
    . '/'
    . $nendoroid->numero;

?>

<section class="layout-container dashboard-page">

    <section class="form-page">

        <section class="form-card transition-form">

            <form
                class="form-layout"
                data-form-page="modifier"
                action="<?= e($formAction) ?>"
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

                    <?php if (isset($errors['company']) && $errors['company'] !== ''): ?>

                        <p class="form-error">

                            <?= e($errors['company']) ?>

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
                        href="<?= e($cancelUrl) ?>"
                    >

                        Annuler

                    </a>

                </div>

            </form>

        </section>

    </section>

</section>