export function initEditPage()
{
    const jacquetteInput = document.getElementById('jacquette');
    const livreNoteInput = document.getElementById('livre_note');
    const noteTotal = document.getElementById('note-total');

    if (!jacquetteInput || !livreNoteInput || !noteTotal)
    {
        return;
    }

    function updateNoteTotal()
    {
        const jacquetteValue = jacquetteInput.value;
        const livreValue = livreNoteInput.value;

        if (jacquetteValue === '' || livreValue === '')
        {
            noteTotal.value = 'Non calculée';
            return;
        }

        const total = parseInt(jacquetteValue, 10) + parseInt(livreValue, 10);

        noteTotal.value = `${total}/10`;
    }

    jacquetteInput.addEventListener('input', updateNoteTotal);
    livreNoteInput.addEventListener('input', updateNoteTotal);

    updateNoteTotal();
}