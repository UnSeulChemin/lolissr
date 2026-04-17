import { showToast } from '../core/toast.js';

export function initEditPage()
{
    const jacquetteInput = document.getElementById('jacquette');
    const livreNoteInput = document.getElementById('livre_note');
    const noteTotal = document.getElementById('note-total');
    const form = document.querySelector('.form-layout');

    if (!jacquetteInput || !livreNoteInput || !noteTotal || !form)
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

    form.addEventListener('submit', async (event) =>
    {
        event.preventDefault();

        const submitButton = form.querySelector('[type="submit"]');

        if (submitButton)
        {
            submitButton.disabled = true;
        }

        const formData = new FormData(form);

        try
        {
            const response = await fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            const contentType = response.headers.get('content-type') || '';
            const isJson = contentType.includes('application/json');

            if (!isJson)
            {
                throw new Error('Réponse non JSON');
            }

            const data = await response.json();

            if (!data.success)
            {
                showToast(
                    data.message || 'Erreur lors de la mise à jour',
                    'error'
                );
                return;
            }

            if (data.redirect)
            {
                window.location.href = data.redirect;
                return;
            }
        }
        catch (error)
        {
            showToast('Erreur serveur', 'error');
        }
        finally
        {
            if (submitButton)
            {
                submitButton.disabled = false;
            }
        }
    });
}