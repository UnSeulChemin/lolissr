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

$formAction =
    $baseUri
    . 'chinois/ajouter/grammaire';

$returnUrl =
    $baseUri
    . 'chinois/ajouter';

$niveauValue =
    (string) (
        $old['niveau']
        ?? 'HSK1'
    );

$niveauOptions = [
    'HSK1',
    'HSK2',
    'HSK3',
    'HSK4',
];

?>

<section class="layout-container dashboard-page">

    <section class="form-page">

        <section class="form-card transition-form">

            <form
                class="form-layout"
                data-form-page="ajouter-grammaire"
                action="<?= e($formAction) ?>"
                method="post"
            >

                <?= csrf_field() ?>

                <div class="form-group">

                    <label
                        class="form-label"
                        for="niveau"
                    >
                        Niveau
                    </label>

                    <select
                        class="
                            form-input
                            form-select
                        "
                        name="niveau"
                        id="niveau"
                        required
                    >

                        <?php foreach ($niveauOptions as $niveau): ?>

                            <option
                                value="<?= e($niveau) ?>"
                                <?= $niveauValue === $niveau
                                    ? 'selected'
                                    : '' ?>
                            >
                                <?= e($niveau) ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                    <?php if (isset($errors['niveau'])): ?>

                        <p class="form-error">
                            <?= e((string) $errors['niveau']) ?>
                        </p>

                    <?php endif; ?>

                </div>

                <div class="form-group">

                    <label
                        class="form-label"
                        for="titre"
                    >
                        Titre
                    </label>

                    <input
                        class="form-input"
                        type="text"
                        name="titre"
                        id="titre"
                        placeholder="Ex : Verbe 是"
                        value="<?= e((string) ($old['titre'] ?? '')) ?>"
                        required
                    >

                    <?php if (isset($errors['titre'])): ?>

                        <p class="form-error">
                            <?= e((string) $errors['titre']) ?>
                        </p>

                    <?php endif; ?>

                </div>

                <div class="form-group">

                    <label
                        class="form-label"
                        for="structure"
                    >
                        Structure
                    </label>

                    <input
                        class="form-input"
                        type="text"
                        name="structure"
                        id="structure"
                        placeholder="Ex : Sujet + 是 + Nom"
                        value="<?= e((string) ($old['structure'] ?? '')) ?>"
                        required
                    >

                </div>

                <div class="form-group">

                    <label
                        class="form-label"
                        for="abreviation"
                    >
                        Abréviation
                    </label>

                    <input
                        class="form-input"
                        type="text"
                        name="abreviation"
                        id="abreviation"
                        placeholder="Ex : SHI"
                        value="<?= e((string) ($old['abreviation'] ?? '')) ?>"
                    >

                </div>

                <div class="form-group">

                    <label
                        class="form-label"
                        for="phrase"
                    >
                        Phrase
                    </label>

                    <input
                        class="form-input"
                        type="text"
                        name="phrase"
                        id="phrase"
                        placeholder="Ex : 我是法国人"
                        value="<?= e((string) ($old['phrase'] ?? '')) ?>"
                        required
                    >

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
                        placeholder="Ex : Wǒ shì Fǎguórén"
                        value="<?= e((string) ($old['pinyin'] ?? '')) ?>"
                        required
                    >

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
                        placeholder="Ex : Je suis français"
                        value="<?= e((string) ($old['traduction'] ?? '')) ?>"
                        required
                    >

                </div>

                <div class="form-group">

                    <label
                        class="form-label"
                        for="explication"
                    >
                        Explication
                    </label>

                    <textarea
                        class="form-textarea"
                        name="explication"
                        id="explication"
                        rows="6"
                        placeholder="Ex : 是 est le verbe être..."
                    ><?= e((string) ($old['explication'] ?? '')) ?></textarea>

                </div>

                <div class="form-group">

                    <label
                        class="form-label"
                        for="section"
                    >
                        Section
                    </label>

                    <input
                        class="form-input"
                        type="text"
                        name="section"
                        id="section"
                        placeholder="Ex : Verbes de base"
                        value="<?= e((string) ($old['section'] ?? '')) ?>"
                        required
                    >

                </div>

                <div class="form-group">

                    <label
                        class="form-label"
                        for="categorie"
                    >
                        Catégorie
                    </label>

                    <input
                        class="form-input"
                        type="text"
                        name="categorie"
                        id="categorie"
                        placeholder="Ex : Être et identité"
                        value="<?= e((string) ($old['categorie'] ?? '')) ?>"
                        required
                    >

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
                        href="<?= e($returnUrl) ?>"
                    >
                        Retour
                    </a>

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

?>