<?php

declare(strict_types=1);

use App\DTO\Common\Responses\FormViewData;

/** @var FormViewData $form */

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
                        value="<?= e((string) ($form->old['username'] ?? '')) ?>"
                        autofocus
                        required
                    >

                    <?php if (!empty($form->errors['username'])): ?>

                        <p class="form-error">

                            <?= e($form->errors['username']) ?>

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

                    <?php if (!empty($form->errors['password'])): ?>

                        <p class="form-error">

                            <?= e($form->errors['password']) ?>

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