<?php

declare(strict_types=1);

use App\DTO\Common\Responses\FormViewData;
use App\DTO\Peluche\Responses\PelucheData;

/** @var PelucheData $peluche */
/** @var FormViewData $form */

$errors = $form->errors;
$old = $form->old;

$originValue = $old['origin'] ?? $peluche->origin;
$waifuValue = $old['waifu'] ?? $peluche->waifu;
$companyValue = $old['company'] ?? $peluche->company;
$releaseDateValue = $old['release_date'] ?? $peluche->release_date;
$commentaireValue = $old['commentaire'] ?? $peluche->commentaire;

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
                        for="origin"
                    >
                        Origin
                    </label>

                    <input
                        class="form-input u-text-center u-w-full"
                        type="text"
                        name="origin"
                        id="origin"
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
                        for="waifu"
                    >
                        Waifu
                    </label>

                    <input
                        class="form-input u-text-center u-w-full"
                        type="text"
                        name="waifu"
                        id="waifu"
                        value="<?= e($waifuValue) ?>"
                        maxlength="100"
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
                        for="company"
                    >
                        Company
                    </label>

                    <input
                        class="form-input u-text-center u-w-full"
                        type="text"
                        name="company"
                        id="company"
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
                        for="release_date"
                    >
                        Date de sortie
                    </label>

                    <input
                        class="form-input u-text-center u-w-full"
                        type="text"
                        name="release_date"
                        id="release_date"
                        placeholder="Ex : 29/07/2021"
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