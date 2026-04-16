const jacquetteInput = document.getElementById('jacquette');
const livreNoteInput = document.getElementById('livre_note');
const noteTotal = document.getElementById('note-total');

function updateNoteTotal()
{
    if (!jacquetteInput || !livreNoteInput || !noteTotal)
    {
        return;
    }

    const jacquetteValue = jacquetteInput.value;
    const livreValue = livreNoteInput.value;

    if (jacquetteValue === '' || livreValue === '')
    {
        noteTotal.value = 'Non calculée';
        return;
    }

    const total = parseInt(jacquetteValue, 10) + parseInt(livreValue, 10);
    noteTotal.value = total + '/10';
}

if (jacquetteInput && livreNoteInput)
{
    jacquetteInput.addEventListener('change', updateNoteTotal);
    livreNoteInput.addEventListener('change', updateNoteTotal);
}

updateNoteTotal();