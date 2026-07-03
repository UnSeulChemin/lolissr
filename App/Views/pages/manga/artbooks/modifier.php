<?php

declare(strict_types=1);

use App\DTO\Manga\Responses\ArtbookData;
use Framework\Support\Session;

/** @var ArtbookData $artbook */

$baseUri = view_base_uri();

$errors = Session::pull('errors', []);

$old = Session::pull('old', []);

$artbookValue = $old['artbook'] ?? $artbook->artbook;

$auteurValue = $old['auteur'] ?? ($artbook->auteur ?? '');

$serieValue = $old['serie'] ?? ($artbook->serie ?? '');

$formAction = $baseUri
    . 'manga/artbooks/'
    . rawurlencode($artbook->slug)
    . '/modifier/'
    . $artbook->numero;

$cancelUrl = $baseUri
    . 'manga/artbooks/'
    . rawurlencode($artbook->slug)
    . '/'
    . $artbook->numero;

?>

<section class="layout-container dashboard-page">

    <section class="form-page">

        <section class="form-card transition-form">

            <form
                class="form-layout"
                action="<?= e($formAction) ?>"
                method="post"
            >

                <?= csrf_field() ?>

                <div class="form-group">

                    <label
                        class="form-label"
                        for="artbook"
                    >

                        Artbook

                    </label>

                    <input
                        class="form-input"
                        type="text"
                        id="artbook"
                        name="artbook"
                        value="<?= e($artbookValue) ?>"
                        maxlength="255"
                        required
                    >

                    <?php if (isset($errors['artbook']) && $errors['artbook'] !== ''): ?>

                        <p class="form-error">

                            <?= e($errors['artbook']) ?>

                        </p>

                    <?php endif; ?>

                </div>


                <div class="form-group">

                    <label
                        class="form-label"
                        for="auteur"
                    >

                        Auteur

                    </label>

                    <input
                        class="form-input"
                        type="text"
                        id="auteur"
                        name="auteur"
                        value="<?= e($auteurValue) ?>"
                        maxlength="100"
                    >

                    <?php if (isset($errors['auteur']) && $errors['auteur'] !== ''): ?>

                        <p class="form-error">

                            <?= e($errors['auteur']) ?>

                        </p>

                    <?php endif; ?>

                </div>


                <div class="form-group">

                    <label
                        class="form-label"
                        for="serie"
                    >

                        Série

                    </label>

                    <input
                        class="form-input"
                        type="text"
                        id="serie"
                        name="serie"
                        value="<?= e($serieValue) ?>"
                        maxlength="100"
                    >

                    <?php if (isset($errors['serie']) && $errors['serie'] !== ''): ?>

                        <p class="form-error">

                            <?= e($errors['serie']) ?>

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
