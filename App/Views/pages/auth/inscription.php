<?php

declare(strict_types=1);

use App\DTO\Common\Responses\FormViewData;

/** @var FormViewData $form */

$usernameValue = (string) ($form->old['username'] ?? '');

$usernameError = $form->errors['username'] ?? '';

$passwordError = $form->errors['password'] ?? '';

?>

<section class="layout-container dashboard-page">

    <section class="form-page">

        <section class="form-card transition-form">

            <form
                class="form-layout"
                action="<?= e($form->formAction) ?>"
                method="post"
            >

                <?= csrf_field() ?>

                <div class="form-group u-stack">

                    <label
                        class="form-label u-text-center u-bold"
                        for="username"
                    >

                        Nom d'utilisateur

                    </label>

                    <input
                        class="form-input u-text-center u-w-full"
                        type="text"
                        name="username"
                        id="username"
                        placeholder="Ex : LoliSSR"
                        value="<?= e($usernameValue) ?>"
                        autofocus
                        required
                    >

                    <?php if ($usernameError !== ''): ?>

                        <p class="form-error u-semibold">

                            <?= e($usernameError) ?>

                        </p>

                    <?php endif; ?>

                </div>

                <div class="form-group u-stack">

                    <label
                        class="form-label u-text-center u-bold"
                        for="password"
                    >

                        Mot de passe

                    </label>

                    <input
                        class="form-input u-text-center u-w-full"
                        type="password"
                        name="password"
                        id="password"
                        required
                    >

                    <?php if ($passwordError !== ''): ?>

                        <p class="form-error u-semibold">

                            <?= e($passwordError) ?>

                        </p>

                    <?php endif; ?>

                </div>

                <div class="form-actions u-stack u-items-center">

                    <button
                        type="submit"
                        class="form-submit u-inline-center u-pointer u-semibold"
                    >

                        Créer le compte

                    </button>

                </div>

            </form>

        </section>

    </section>

</section>