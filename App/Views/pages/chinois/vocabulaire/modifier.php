<?php

declare(strict_types=1);

use App\DTO\Chinois\Responses\ChinoisVocabulaireData;
use App\DTO\Common\Responses\FormViewData;

/** @var ChinoisVocabulaireData $vocabulaire */
/** @var FormViewData $form */
/** @var string|null $returnTo */

$errors = $form->errors;
$old = $form->old;
$returnTo ??= '';

$langueValue = $old['langue'] ?? $vocabulaire->langue;

$langueOptions = [
    'mandarin' => 'Mandarin',
    'jinyu' => 'JinYu',
];

?>

<section class="layout-container dashboard-page">

    <section class="form-page">

        <section class="form-card transition-form">

            <form
                class="form-layout"
                data-form-page="modifier-vocabulaire"
                action="<?= e($form->formAction) ?>"
                method="post"
            >

                <?= csrf_field() ?>

                <?php if ($returnTo !== ''): ?>

                    <input
                        type="hidden"
                        name="return_to"
                        value="<?= e($returnTo) ?>"
                    >

                <?php endif; ?>

                <div class="form-group u-stack">

                    <label
                        class="form-label u-text-center u-bold"
                        for="langue"
                    >
                        Langue
                    </label>

                    <select
                        class="
                            form-input
                            form-select
                         u-text-center u-w-full"
                        name="langue"
                        id="langue"
                        required
                    >

                        <?php foreach ($langueOptions as $value => $label): ?>

                            <option
                                value="<?= e($value) ?>"
                                <?= $langueValue === $value ? 'selected' : '' ?>
                            >
                                <?= e($label) ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                    <?php if (isset($errors['langue']) && $errors['langue'] !== ''): ?>

                        <p class="form-error u-semibold">
                            <?= e($errors['langue']) ?>
                        </p>

                    <?php endif; ?>

                </div>

                <div class="form-group u-stack">

                    <label
                        class="form-label u-text-center u-bold"
                        for="mot"
                    >
                        Mot
                    </label>

                    <input
                        class="form-input u-text-center u-w-full"
                        type="text"
                        name="mot"
                        id="mot"
                        value="<?= e($old['mot'] ?? $vocabulaire->mot) ?>"
                        autofocus
                        required
                    >

                    <?php if (isset($errors['mot']) && $errors['mot'] !== ''): ?>

                        <p class="form-error u-semibold">
                            <?= e($errors['mot']) ?>
                        </p>

                    <?php endif; ?>

                </div>

                <div class="form-group u-stack">

                    <label
                        class="form-label u-text-center u-bold"
                        for="pinyin"
                    >
                        Pinyin
                    </label>

                    <input
                        class="form-input u-text-center u-w-full"
                        type="text"
                        name="pinyin"
                        id="pinyin"
                        value="<?= e($old['pinyin'] ?? $vocabulaire->pinyin) ?>"
                        required
                    >

                    <?php if (isset($errors['pinyin']) && $errors['pinyin'] !== ''): ?>

                        <p class="form-error u-semibold">
                            <?= e($errors['pinyin']) ?>
                        </p>

                    <?php endif; ?>

                </div>

                <div class="form-group u-stack">

                    <label
                        class="form-label u-text-center u-bold"
                        for="type"
                    >
                        Type
                    </label>

                    <input
                        class="form-input u-text-center u-w-full"
                        type="text"
                        name="type"
                        id="type"
                        value="<?= e($old['type'] ?? $vocabulaire->type) ?>"
                        required
                    >

                    <?php if (isset($errors['type']) && $errors['type'] !== ''): ?>

                        <p class="form-error u-semibold">
                            <?= e($errors['type']) ?>
                        </p>

                    <?php endif; ?>

                </div>

                <div class="form-group u-stack">

                    <label
                        class="form-label u-text-center u-bold"
                        for="traduction"
                    >
                        Traduction
                    </label>

                    <textarea
                        class="
                            form-textarea
                            form-textarea--translation
                        "
                        name="traduction"
                        id="traduction"
                        rows="2"
                        required
                    ><?= e($old['traduction'] ?? $vocabulaire->traduction) ?></textarea>

                    <?php if (isset($errors['traduction']) && $errors['traduction'] !== ''): ?>

                        <p class="form-error u-semibold">
                            <?= e($errors['traduction']) ?>
                        </p>

                    <?php endif; ?>

                </div>

                <div class="form-group u-stack">

                    <label
                        class="form-label u-text-center u-bold"
                        for="exemple"
                    >
                        Exemple
                    </label>

                    <input
                        class="form-input u-text-center u-w-full"
                        type="text"
                        name="exemple"
                        id="exemple"
                        maxlength="255"
                        value="<?= e($old['exemple'] ?? $vocabulaire->exemple) ?>"
                        required
                    >

                    <?php if (isset($errors['exemple']) && $errors['exemple'] !== ''): ?>

                        <p class="form-error u-semibold">
                            <?= e($errors['exemple']) ?>
                        </p>

                    <?php endif; ?>

                </div>

                <div class="form-actions u-stack u-items-center">

                    <button
                        type="submit"
                        class="form-submit u-inline-center u-pointer u-semibold"
                    >
                        Modifier
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