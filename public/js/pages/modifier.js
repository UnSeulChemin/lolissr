export function initEditPage()
{
    /*
    |------------------------------------------------------------------
    | Éléments
    |------------------------------------------------------------------
    */

    const form = document.querySelector(
        '.form-layout[data-form-page="modifier"]'
    );

    if (!form)
    {
        return;
    }

    const jacquetteInput = document.getElementById('jacquette');
    const livreNoteInput = document.getElementById('livre_note');
    const totalNoteInput = document.getElementById('note-total');

    if (!jacquetteInput || !livreNoteInput || !totalNoteInput)
    {
        return;
    }

    /*
    |------------------------------------------------------------------
    | Sécurité anti double init
    |------------------------------------------------------------------
    */

    if (form.dataset.editPageInit === 'true')
    {
        return;
    }

    form.dataset.editPageInit = 'true';

    /*
    |------------------------------------------------------------------
    | Calcul note totale
    |------------------------------------------------------------------
    */

    function updateTotalNotePreview()
    {
        const jacquetteValue = jacquetteInput.value;
        const livreValue = livreNoteInput.value;

        if (jacquetteValue === '' || livreValue === '')
        {
            totalNoteInput.value = 'Non calculée';
            return;
        }

        const total =
            parseInt(jacquetteValue, 10)
            + parseInt(livreValue, 10);

        totalNoteInput.value = `${total}/10`;
    }

    jacquetteInput.addEventListener('input', updateTotalNotePreview);
    livreNoteInput.addEventListener('input', updateTotalNotePreview);

    updateTotalNotePreview();
}