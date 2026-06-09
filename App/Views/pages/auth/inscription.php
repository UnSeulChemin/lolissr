<?php

declare(strict_types=1);

use Framework\Support\Session;

$errors =
    Session::pull('errors', []);

$old =
    Session::pull('old', []);

$baseUri =
    rtrim(
        (string) ($baseUri ?? ''),
        '/',
    ) . '/';

?>

<section class="layout-container dashboard-page">

    <section class="form-page">

        <section class="form-card transition-form">

                <form
                    class="form-layout"
                    action="<?= e($baseUri) ?>inscription"
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
                        placeholder="Ex : LoliSSR"
                        value="<?= e((string) ($old['username'] ?? '')) ?>"
                        autofocus
                        required
                    >

                    <?php if (
                        isset($errors['username'])
                        && $errors['username'] !== ''
                    ): ?>

                        <p class="form-error">

                            <?= e((string) $errors['username']) ?>

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

                    <?php if (
                        isset($errors['password'])
                        && $errors['password'] !== ''
                    ): ?>

                        <p class="form-error">

                            <?= e((string) $errors['password']) ?>

                        </p>

                    <?php endif; ?>

                </div>

                <div class="form-actions">

                    <button
                        type="submit"
                        class="form-submit"
                    >

                        Créer le compte

                    </button>

                </div>

            </form>

        </section>

    </section>

</section>

<?php

Session::forget([
    'errors',
    'old',
]);