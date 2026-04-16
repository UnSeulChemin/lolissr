<?php

use App\Core\Session;

$errors = Session::get('errors', []);
$old = Session::get('old', []);

$jacquetteValue = $old['jacquette'] ?? ($manga->jacquette ?? '');
$livreNoteValue = $old['livre_note'] ?? ($manga->livre_note ?? '');
$commentaireValue = $old['commentaire'] ?? ($manga->commentaire ?? '');

$cancelUrl = $basePath . 'manga/' . rawurlencode($manga->slug) . '/' . (int) $manga->numero;

?>

<section class="layout-container dashboard-page">

    <section class="form-page animate-fade-up">

        <section class="form-card">

            <form
                class="form-layout"
                action="<?= $basePath . 'manga/update/' . rawurlencode($manga->slug) . '/' . (int) $manga->numero; ?>"
                method="post">

                <div class="form-group">

                    <label class="form-label" for="jacquette">
                        Note jacquette
                    </label>

                    <select class="form-input form-select" name="jacquette" id="jacquette">
                        <option value="">Choisir</option>
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <option value="<?= $i; ?>" <?= ((string) $jacquetteValue === (string) $i) ? 'selected' : ''; ?>>
                                <?= $i; ?>
                            </option>
                        <?php endfor; ?>
                    </select>

                    <?php if (!empty($errors['jacquette'])): ?>
                        <p class="form-error">
                            <?= htmlspecialchars($errors['jacquette']) ?>
                        </p>
                    <?php endif; ?>

                </div>

                <div class="form-group">

                    <label class="form-label" for="livre_note">
                        Note livre
                    </label>

                    <select class="form-input form-select" name="livre_note" id="livre_note">
                        <option value="">Choisir</option>
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <option value="<?= $i; ?>" <?= ((string) $livreNoteValue === (string) $i) ? 'selected' : ''; ?>>
                                <?= $i; ?>
                            </option>
                        <?php endfor; ?>
                    </select>

                    <?php if (!empty($errors['livre_note'])): ?>
                        <p class="form-error">
                            <?= htmlspecialchars($errors['livre_note']) ?>
                        </p>
                    <?php endif; ?>

                </div>

                <div class="form-group">

                    <label class="form-label" for="commentaire">
                        Commentaire
                    </label>

                    <textarea
                        class="form-textarea"
                        name="commentaire"
                        id="commentaire"
                        rows="5"
                        maxlength="1000"
                        placeholder="Ex : défaut en haut de la jacquette"><?= htmlspecialchars($commentaireValue) ?></textarea>

                    <?php if (!empty($errors['commentaire'])): ?>
                        <p class="form-error">
                            <?= htmlspecialchars($errors['commentaire']) ?>
                        </p>
                    <?php endif; ?>

                </div>

                <div class="form-group">

                    <label class="form-label" for="note-total">
                        Note totale actuelle
                    </label>

                    <input
                        class="form-input"
                        type="text"
                        id="note-total"
                        value="<?= $manga->note !== null ? (int) $manga->note . '/10' : 'Non calculée' ?>"
                        readonly>

                </div>

                <div class="form-actions">

                    <button
                        type="submit"
                        class="form-submit">
                        Enregistrer
                    </button>

                    <a
                        class="form-submit form-submit-secondary"
                        href="<?= $cancelUrl; ?>">
                        Annuler
                    </a>

                </div>

            </form>

        </section>

    </section>

</section>

<?php Session::forget(['errors', 'old']); ?>

<script type="module" src="<?= $basePath; ?>public/js/pages/edit.js"></script>