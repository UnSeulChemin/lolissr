<?php

declare(strict_types=1);

use App\DTO\Figurine\Responses\FigurineData;
use Framework\Support\Session;

/** @var FigurineData $figurine */

$baseUri = view_base_uri();

$errors = Session::pull('errors', []);

$old = Session::pull('old', []);

$originValue = $old['origin'] ?? $figurine->origin;

$waifuValue = $old['waifu'] ?? $figurine->waifu;

$scaleValue = $old['scale'] ?? $figurine->scale;

$heightValue = $old['height_cm'] ?? $figurine->height_cm;

$companyValue = $old['company'] ?? $figurine->company;

$releaseDateValue = $old['release_date'] ?? $figurine->release_date;

$commentaireValue = $old['commentaire'] ?? $figurine->commentaire;

$formAction = $baseUri . 'figurine/waifus/' . rawurlencode($figurine->slug) . '/modifier/' . $figurine->numero;

$cancelUrl = $baseUri . 'figurine/waifus/' . rawurlencode($figurine->slug) . '/' . $figurine->numero;

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
                        for="origin"
                    >
                        Origin
                    </label>

                    <input
                        class="form-input"
                        type="text"
                        name="origin"
                        id="origin"
                        value="<?= e($originValue) ?>"
                        maxlength="150"
                        required
                    >

                    <?php if (isset($errors['origin']) && $errors['origin'] !== ''): ?>

                        <p class="form-error">
                            <?= e($errors['origin']) ?>
                        </p>

                    <?php endif; ?>

                </div>

                <div class="form-group">

                    <label
                        class="form-label"
                        for="waifu"
                    >
                        Waifu
                    </label>

                    <input
                        class="form-input"
                        type="text"
                        name="waifu"
                        id="waifu"
                        value="<?= e($waifuValue) ?>"
                        maxlength="100"
                        required
                    >

                    <?php if (isset($errors['waifu']) && $errors['waifu'] !== ''): ?>

                        <p class="form-error">
                            <?= e($errors['waifu']) ?>
                        </p>

                    <?php endif; ?>

                </div>

                <div class="form-group">

                    <label
                        class="form-label"
                        for="scale"
                    >
                        Échelle
                    </label>

                    <input
                        class="form-input"
                        type="text"
                        name="scale"
                        id="scale"
                        value="<?= e($scaleValue) ?>"
                        maxlength="10"
                        required
                    >

                    <?php if (isset($errors['scale']) && $errors['scale'] !== ''): ?>

                        <p class="form-error">
                            <?= e($errors['scale']) ?>
                        </p>

                    <?php endif; ?>

                </div>

                <div class="form-group">

                    <label
                        class="form-label"
                        for="height_cm"
                    >
                        Hauteur (cm)
                    </label>

                    <input
                        class="form-input"
                        type="number"
                        name="height_cm"
                        id="height_cm"
                        min="0"
                        step="0.1"
                        value="<?= e($heightValue) ?>"
                    >

                    <?php if (isset($errors['height_cm']) && $errors['height_cm'] !== ''): ?>

                        <p class="form-error">
                            <?= e($errors['height_cm']) ?>
                        </p>

                    <?php endif; ?>

                </div>

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
                        for="release_date"
                    >
                        Date de sortie
                    </label>

                    <input
                        class="form-input"
                        type="text"
                        name="release_date"
                        id="release_date"
                        placeholder="Ex : 29/07/2021"
                        value="<?= e($releaseDateValue) ?>"
                        maxlength="10"
                    >

                    <?php if (isset($errors['release_date']) && $errors['release_date'] !== ''): ?>

                        <p class="form-error">
                            <?= e($errors['release_date']) ?>
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