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

                <div class="form-group">

                    <label
                        class="form-label"
                        for="username"
                    >

                        Nom d'utilisateur

                    </label>

                    <input
                        class="form-input"
                        type="text"
                        name="username"
                        id="username"
                        value="<?= e($usernameValue) ?>"
                        autofocus
                        required
                    >

                    <?php if ($usernameError !== ''): ?>

                        <p class="form-error">

                            <?= e($usernameError) ?>

                        </p>

                    <?php endif; ?>

                </div>

                <div class="form-group">

                    <label
                        class="form-label"
                        for="password"
                    >

                        Mot de passe

                    </label>

                    <input
                        class="form-input"
                        type="password"
                        name="password"
                        id="password"
                        required
                    >

                    <?php if ($passwordError !== ''): ?>

                        <p class="form-error">

                            <?= e($passwordError) ?>

                        </p>

                    <?php endif; ?>

                </div>

                <div class="form-actions">

                    <button
                        type="submit"
                        class="form-submit"
                    >

                        Connexion

                    </button>

                </div>

            </form>

        </section>

    </section>

</section>