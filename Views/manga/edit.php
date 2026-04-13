<?php

use App\Core\Form;
use App\Core\Session;

$errors = Session::get('errors', []);
$old = Session::get('old', []);
$error = Session::pull('error');
$success = Session::pull('success');

$jacquetteValue = $old['jacquette'] ?? ($manga->jacquette ?? '');
$livreNoteValue = $old['livre_note'] ?? ($manga->livre_note ?? '');
$commentaireValue = $old['commentaire'] ?? ($manga->commentaire ?? '');

$cancelUrl = $basePath . 'manga/' . rawurlencode($manga->slug) . '/' . (int) $manga->numero;
?>

<section class="form-box">

    <h1 class="card-banner">
        Modifier <?= htmlspecialchars($manga->livre) ?>
        - Tome <?= str_pad((string) ((int) $manga->numero), 2, '0', STR_PAD_LEFT) ?>
    </h1>

    <?php if (!empty($error)): ?>
        <div class="alert-error">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="alert-success">
            <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <?php
    $form = new Form();

    echo $form
        ->startForm($basePath . 'manga/update/' . rawurlencode($manga->slug) . '/' . (int) $manga->numero, 'post')

        ->startDiv(['class' => 'form-row'])
        ->addLabelFor('jacquette', 'Note jacquette')
        ->addSelect(
            'jacquette',
            [
                '' => 'Choisir',
                1 => '1',
                2 => '2',
                3 => '3',
                4 => '4',
                5 => '5'
            ],
            ['id' => 'jacquette', 'class' => 'form-select'],
            $jacquetteValue
        )
        ->endDiv()

        ->startDiv(['class' => 'form-row'])
        ->addLabelFor('livre_note', 'Note livre')
        ->addSelect(
            'livre_note',
            [
                '' => 'Choisir',
                1 => '1',
                2 => '2',
                3 => '3',
                4 => '4',
                5 => '5'
            ],
            ['id' => 'livre_note', 'class' => 'form-select'],
            $livreNoteValue
        )
        ->endDiv()

        ->startDiv(['class' => 'form-column'])
        ->addLabelFor('commentaire', 'Commentaire')
        ->addTextarea(
            'commentaire',
            $commentaireValue,
            [
                'id' => 'commentaire',
                'rows' => 5,
                'maxlength' => 1000,
                'class' => 'form-textarea',
                'placeholder' => 'Ex : défaut en haut de la jacquette'
            ]
        )
        ->endDiv()

        ->startDiv(['class' => 'form-actions'])
        ->addButton('Enregistrer', ['type' => 'submit', 'class' => 'link-edit full-width'])
        ->endDiv()

        ->startDiv(['class' => 'form-actions-secondary'])
        ->addButton('Annuler', ['type' => 'button', 'class' => 'link-section', 'onclick' => "window.location.href='{$cancelUrl}'"])
        ->endDiv()

        ->endForm()
        ->render();
    ?>

    <?php if (!empty($errors['jacquette'])): ?>
        <p class="form-error">
            <?= htmlspecialchars($errors['jacquette']) ?>
        </p>
    <?php endif; ?>

    <?php if (!empty($errors['livre_note'])): ?>
        <p class="form-error">
            <?= htmlspecialchars($errors['livre_note']) ?>
        </p>
    <?php endif; ?>

    <?php if (!empty($errors['commentaire'])): ?>
        <p class="form-error">
            <?= htmlspecialchars($errors['commentaire']) ?>
        </p>
    <?php endif; ?>

    <p class="note-info">
        Note totale actuelle :
        <span id="note-total">
            <?= $manga->note !== null ? (int) $manga->note . '/10' : 'Non calculée' ?>
        </span>
    </p>

</section>

<?php Session::forget(['errors', 'old']); ?>

<script>
function updateNoteTotal() {
    const jacquetteValue = document.getElementById('jacquette').value;
    const livreValue = document.getElementById('livre_note').value;

    if (jacquetteValue === '' || livreValue === '') {
        document.getElementById('note-total').textContent = 'Non calculée';
        return;
    }

    const jacquette = parseInt(jacquetteValue, 10);
    const livre = parseInt(livreValue, 10);
    const total = jacquette + livre;

    document.getElementById('note-total').textContent = total + '/10';
}

document.getElementById('jacquette').addEventListener('change', updateNoteTotal);
document.getElementById('livre_note').addEventListener('change', updateNoteTotal);

updateNoteTotal();
</script>