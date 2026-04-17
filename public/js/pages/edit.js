import { showToast } from '../core/toast.js';

export function initEditPage()
{
    /*
    |------------------------------------------------------------------
    | Éléments
    |------------------------------------------------------------------
    */

    const jacquetteInput = document.getElementById('jacquette');
    const livreNoteInput = document.getElementById('livre_note');
    const totalNoteInput = document.getElementById('note-total');
    const form = document.querySelector('.form-layout');

    if (!jacquetteInput || !livreNoteInput || !totalNoteInput || !form)
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

    /*
    |------------------------------------------------------------------
    | Soumission AJAX
    |------------------------------------------------------------------
    */

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
            const isJsonResponse = contentType.includes('application/json');

            if (!isJsonResponse)
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

            showToast(
                data.message || 'Modification enregistrée',
                'success'
            );
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