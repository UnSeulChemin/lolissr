<?php

declare(strict_types=1);

use App\DTO\Common\Responses\FormViewData;

/** @var FormViewData $form */

$errors = $form->errors;

$old = $form->old;

$langueValue = $old['langue'] ?? 'mandarin';

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
                data-form-page="ajouter-vocabulaire"
                action="<?= e($form->formAction) ?>"
                method="post"
            >

                <?= csrf_field() ?>

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

                            <?= e($errors['langue']) ?>

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
                        placeholder="Ex : 苹果"
                        value="<?= e($old['mot'] ?? '') ?>"
                        autofocus
                        required
                    >

                    <?php if (
                        isset($errors['mot'])
                        && $errors['mot'] !== ''
                    ): ?>

                        <p class="form-error">

                            <?= e($errors['mot']) ?>

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
                        placeholder="Ex : Píngguǒ"
                        value="<?= e($old['pinyin'] ?? '') ?>"
                        required
                    >

                    <?php if (
                        isset($errors['pinyin'])
                        && $errors['pinyin'] !== ''
                    ): ?>

                        <p class="form-error">

                            <?= e($errors['pinyin']) ?>

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
                        placeholder="Ex : Nom"
                        value="<?= e($old['type'] ?? '') ?>"
                        required
                    >

                    <?php if (
                        isset($errors['type'])
                        && $errors['type'] !== ''
                    ): ?>

                        <p class="form-error">

                            <?= e($errors['type']) ?>

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
                        placeholder="Ex : Pomme"
                        value="<?= e($old['traduction'] ?? '') ?>"
                        required
                    >

                    <?php if (
                        isset($errors['traduction'])
                        && $errors['traduction'] !== ''
                    ): ?>

                        <p class="form-error">

                            <?= e($errors['traduction']) ?>

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
                        placeholder="Ex : 我喜欢吃苹果。"
                    ><?= e($old['exemple'] ?? '') ?></textarea>

                    <?php if (
                        isset($errors['exemple'])
                        && $errors['exemple'] !== ''
                    ): ?>

                        <p class="form-error">

                            <?= e($errors['exemple']) ?>

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