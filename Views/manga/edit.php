<section class="form-box">

    <h1 class="card-banner">
        Modifier <?= htmlspecialchars($manga->livre) ?>
        - Tome <?= str_pad((string) ((int) $manga->numero), 2, '0', STR_PAD_LEFT) ?>
    </h1>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert-error">
            <?= htmlspecialchars($_SESSION['error']) ?>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert-success">
            <?= htmlspecialchars($_SESSION['success']) ?>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

<?php

use App\Core\Form;

$form = new Form();

echo $form
    ->startForm(
        $basePath . 'manga/update/' . rawurlencode($manga->slug) . '/' . (int) $manga->numero,
        'post'
    )

    /* NOTE JACQUETTE */

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
        $manga->jacquette
    )
    ->endDiv()

    /* NOTE LIVRE */

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
        $manga->livre_note
    )
    ->endDiv()

    /* COMMENTAIRE */

    ->startDiv(['class' => 'form-column'])
    ->addLabelFor('commentaire', 'Commentaire')
    ->addTextarea(
        'commentaire',
        $manga->commentaire ?? '',
        [
            'id' => 'commentaire',
            'rows' => 5,
            'maxlength' => 255,
            'class' => 'form-textarea',
            'placeholder' => 'Ex : défaut en haut de la jacquette'
        ]
    )
    ->endDiv()

    /* BOUTONS */

    ->startDiv(['class' => 'form-actions'])
    ->addButton(
        'Enregistrer',
        [
            'type' => 'submit',
            'class' => 'link-edit full-width'
        ]
    )
    ->endDiv()

    ->startDiv(['class' => 'form-actions-secondary'])
    ->addButton(
        'Annuler',
        [
            'type' => 'button',
            'class' => 'link-section',
            'onclick' => "window.location.href='{$basePath}manga/collection/" . rawurlencode($manga->slug) . "/" . (int) $manga->numero . "'"
        ]
    )
    ->endDiv()

    ->endForm()
    ->create();

?>

    <p class="note-info">
        Note totale actuelle :
        <span id="note-total">
            <?= $manga->note !== null ? (int) $manga->note . '/10' : 'Non calculée' ?>
        </span>
    </p>

</section>

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

/* ✅ recalcul au chargement */
updateNoteTotal();
</script>