<?php

declare(strict_types=1);

use App\DTO\Common\Responses\FormViewData;
use App\Models\ChinoisVocabulaire;

/** @var ChinoisVocabulaire $vocabulaire */
/** @var FormViewData $form */
/** @var string $returnTo */

$errors = $form->errors;

$old = $form->old;

$returnTo = (string) ($returnTo ?? '');

$langueValue = (string) ($old['langue'] ?? $vocabulaire->langue);

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

                <?php if ($returnTo !== '') : ?>

                    <input
                        type="hidden"
                        name="return_to"
                        value="<?= e($returnTo) ?>"
                    >

                <?php endif; ?>

                <div class="form-group">

                    <label
                        class="form-label"
                        for="langue"
                    >

                        Langue

                    </label>

                    <select
                        class="
                            form-input
                            form-select
                        "
                        name="langue"
                        id="langue"
                        required
                    >

                        <?php foreach ($langueOptions as $value => $label): ?>

                            <option
                                value="<?= e($value) ?>"
                                <?= $langueValue === $value
                                    ? 'selected'
                                    : '' ?>
                            >

                                <?= e($label) ?>

                            </option>

                        <?php endforeach; ?>

                    </select>

                    <?php if (
                        isset($errors['langue'])
                        && $errors['langue'] !== ''
                    ): ?>

                        <p class="form-error">

                            <?= e((string) $errors['langue']) ?>

                        </p>

                    <?php endif; ?>

                </div>

                <div class="form-group">

                    <label
                        class="form-label"
                        for="mot"
                    >

                        Mot

                    </label>

                    <input
                        class="form-input"
                        type="text"
                        name="mot"
                        id="mot"
                        value="<?= e((string) (
                            $old['mot']
                            ?? $vocabulaire->mot
                        )) ?>"
                        autofocus
                        required
                    >

                    <?php if (
                        isset($errors['mot'])
                        && $errors['mot'] !== ''
                    ): ?>

                        <p class="form-error">

                            <?= e((string) $errors['mot']) ?>

                        </p>

                    <?php endif; ?>

                </div>

                <div class="form-group">

                    <label
                        class="form-label"
                        for="pinyin"
                    >

                        Pinyin

                    </label>

                    <input
                        class="form-input"
                        type="text"
                        name="pinyin"
                        id="pinyin"
                        value="<?= e((string) (
                            $old['pinyin']
                            ?? $vocabulaire->pinyin
                        )) ?>"
                        required
                    >

                    <?php if (
                        isset($errors['pinyin'])
                        && $errors['pinyin'] !== ''
                    ): ?>

                        <p class="form-error">

                            <?= e((string) $errors['pinyin']) ?>

                        </p>

                    <?php endif; ?>

                </div>

                <div class="form-group">

                    <label
                        class="form-label"
                        for="type"
                    >

                        Type

                    </label>

                    <input
                        class="form-input"
                        type="text"
                        name="type"
                        id="type"
                        value="<?= e((string) (
                            $old['type']
                            ?? $vocabulaire->type
                        )) ?>"
                        required
                    >

                    <?php if (
                        isset($errors['type'])
                        && $errors['type'] !== ''
                    ): ?>

                        <p class="form-error">

                            <?= e((string) $errors['type']) ?>

                        </p>

                    <?php endif; ?>

                </div>

                <div class="form-group">

                    <label
                        class="form-label"
                        for="traduction"
                    >

                        Traduction

                    </label>

                    <input
                        class="form-input"
                        type="text"
                        name="traduction"
                        id="traduction"
                        value="<?= e((string) (
                            $old['traduction']
                            ?? $vocabulaire->traduction
                        )) ?>"
                        required
                    >

                    <?php if (
                        isset($errors['traduction'])
                        && $errors['traduction'] !== ''
                    ): ?>

                        <p class="form-error">

                            <?= e((string) $errors['traduction']) ?>

                        </p>

                    <?php endif; ?>

                </div>

                <div class="form-group">

                    <label
                        class="form-label"
                        for="exemple"
                    >

                        Exemple

                    </label>

                    <textarea
                        class="form-textarea"
                        name="exemple"
                        id="exemple"
                        rows="6"
                    ><?= e((string) (
                        $old['exemple']
                        ?? $vocabulaire->exemple
                    )) ?></textarea>

                    <?php if (
                        isset($errors['exemple'])
                        && $errors['exemple'] !== ''
                    ): ?>

                        <p class="form-error">

                            <?= e((string) $errors['exemple']) ?>

                        </p>

                    <?php endif; ?>

                </div>

                <div class="form-actions">

                    <button
                        type="submit"
                        class="form-submit"
                    >

                        Modifier

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