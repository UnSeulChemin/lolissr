import { showToast } from '../core/toast.js';

export function initAjouterPage()
{
    /*
    |------------------------------------------------------------------
    | Éléments
    |------------------------------------------------------------------
    */

    const form = document.querySelector(
        '.form-layout[data-form-page="ajouter"]'
    );

    if (!form)
    {
        return;
    }

    const imageInput = document.getElementById('image');
    const uploadText = document.querySelector('.form-upload-text');

    /*
    |------------------------------------------------------------------
    | Sécurité anti double init
    |------------------------------------------------------------------
    */

    if (form.dataset.ajouterPageInit === 'true')
    {
        return;
    }

    form.dataset.ajouterPageInit = 'true';

    /*
    |------------------------------------------------------------------
    | Preview nom fichier
    |------------------------------------------------------------------
    */

    if (imageInput && uploadText && imageInput.dataset.uploadPreviewInit !== 'true')
    {
        imageInput.dataset.uploadPreviewInit = 'true';

        imageInput.addEventListener('change', () =>
        {
            uploadText.textContent = imageInput.files.length > 0
                ? imageInput.files[0].name
                : 'Choisir une image';
        });
    }

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
                    data.message || 'Une erreur est survenue',
                    'error'
                );
                return;
            }

            showToast(
                data.message || 'Manga ajouté avec succès',
                'success'
            );

            form.reset();

            if (uploadText)
            {
                uploadText.textContent = 'Choisir une image';
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