<?php

declare(strict_types=1);

use App\DTO\Chinois\Responses\ChinoisGrammaireData;
use App\DTO\Common\Responses\FormViewData;

/** @var ChinoisGrammaireData $grammaire */
/** @var FormViewData $form */
/** @var string|null $returnTo */

$old = $form->old;
$returnTo ??= '';

$niveauValue = $old['niveau'] ?? $grammaire->niveau;
$niveauOptions = ['HSK1', 'HSK2', 'HSK3', 'HSK4'];

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
                        for="niveau"
                    >
                        Niveau
                    </label>

                    <select
                        class="
                            form-input
                            form-select
                         u-text-center u-w-full"
                        name="niveau"
                        id="niveau"
                        required
                    >

                        <?php foreach ($niveauOptions as $niveau): ?>

                            <option
                                value="<?= e($niveau) ?>"
                                <?= $niveauValue === $niveau ? 'selected' : '' ?>
                            >
                                <?= e($niveau) ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>

                <div class="form-group u-stack">

                    <label
                        class="form-label u-text-center u-bold"
                        for="titre"
                    >
                        Titre
                    </label>

                    <input
                        class="form-input u-text-center u-w-full"
                        type="text"
                        name="titre"
                        id="titre"
                        value="<?= e($old['titre'] ?? $grammaire->titre) ?>"
                        required
                    >

                </div>

                <div class="form-group u-stack">

                    <label
                        class="form-label u-text-center u-bold"
                        for="structure"
                    >
                        Structure
                    </label>

                    <input
                        class="form-input u-text-center u-w-full"
                        type="text"
                        name="structure"
                        id="structure"
                        value="<?= e($old['structure'] ?? $grammaire->structure) ?>"
                        required
                    >

                </div>

                <div class="form-group u-stack">

                    <label
                        class="form-label u-text-center u-bold"
                        for="abreviation"
                    >
                        Abréviation
                    </label>

                    <input
                        class="form-input u-text-center u-w-full"
                        type="text"
                        name="abreviation"
                        id="abreviation"
                        value="<?= e($old['abreviation'] ?? $grammaire->abreviation) ?>"
                    >

                </div>

                <div class="form-group u-stack">

                    <label
                        class="form-label u-text-center u-bold"
                        for="phrase"
                    >
                        Phrase
                    </label>

                    <input
                        class="form-input u-text-center u-w-full"
                        type="text"
                        name="phrase"
                        id="phrase"
                        value="<?= e($old['phrase'] ?? $grammaire->phrase) ?>"
                        required
                    >

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
                        value="<?= e($old['pinyin'] ?? $grammaire->pinyin) ?>"
                        required
                    >

                </div>

                <div class="form-group u-stack">

                    <label
                        class="form-label u-text-center u-bold"
                        for="traduction"
                    >
                        Traduction
                    </label>

                    <input
                        class="form-input u-text-center u-w-full"
                        type="text"
                        name="traduction"
                        id="traduction"
                        value="<?= e($old['traduction'] ?? $grammaire->traduction) ?>"
                        required
                    >

                </div>

                <div class="form-group u-stack">

                    <label
                        class="form-label u-text-center u-bold"
                        for="explication"
                    >
                        Explication
                    </label>

                    <textarea
                        class="form-textarea"
                        name="explication"
                        id="explication"
                        rows="6"
                        required
                    ><?= e($old['explication'] ?? $grammaire->explication) ?></textarea>

                </div>

                <div class="form-group u-stack">

                    <label
                        class="form-label u-text-center u-bold"
                        for="section"
                    >
                        Section
                    </label>

                    <input
                        class="form-input u-text-center u-w-full"
                        type="text"
                        name="section"
                        id="section"
                        value="<?= e($old['section'] ?? $grammaire->section) ?>"
                        required
                    >

                </div>

                <div class="form-group u-stack">

                    <label
                        class="form-label u-text-center u-bold"
                        for="categorie"
                    >
                        Catégorie
                    </label>

                    <input
                        class="form-input u-text-center u-w-full"
                        type="text"
                        name="categorie"
                        id="categorie"
                        value="<?= e($old['categorie'] ?? $grammaire->categorie) ?>"
                        required
                    >

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