<section class="section-content">

    <h1 class="card-banner">
        Modifier <?= htmlspecialchars($manga->livre) ?>
        - Tome <?= str_pad((string) ((int) $manga->numero), 2, '0', STR_PAD_LEFT) ?>
    </h1>

<?php

use App\Core\Form;

$form = new Form();

echo $form
    ->startForm($basePath . 'manga/update/' . rawurlencode($manga->slug) . '/' . (int) $manga->numero, 'post')
    ->startDiv(['class' => 'm-t-30'])
    ->addLabelFor('jacquette', 'Note jacquette :')
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
        ['id' => 'jacquette', 'required' => true],
        $manga->jacquette
    )
    ->endDiv()
    ->startDiv(['class' => 'm-t-30'])
    ->addLabelFor('livre_note', 'Note livre :')
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
        ['id' => 'livre_note', 'required' => true],
        $manga->livre_note
    )
    ->endDiv()
    ->startDiv(['class' => 'm-t-30'])
    ->addButton(
        'Enregistrer',
        [
            'type' => 'submit',
            'class' => 'link-edit'
        ]
    )
    ->endDiv()
    ->endForm()
    ->create();

?>

    <div class="m-t-30">
        <p>
            Note totale actuelle :
            <?= $manga->note !== null ? (int) $manga->note . '/10' : 'Non calculée' ?>
        </p>
    </div>

    <div class="m-t-30">
        <a class="link-section"
           href="<?= $basePath; ?>manga/collection/<?= rawurlencode($manga->slug) ?>/<?= (int) $manga->numero ?>">
            Retour
        </a>
    </div>

</section>