import { showToast } from '../core/toast.js';

export function initAjouterPage()
{
    const form = document.querySelector(
        '.form-layout[data-form-page="ajouter"]'
    );

    if (!form)
    {
        return;
    }

    const imageInput = document.getElementById('image');
    const uploadText = document.querySelector('.form-upload-text');

    if (form.dataset.ajouterPageInit === 'true')
    {
        return;
    }

    form.dataset.ajouterPageInit = 'true';

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

    form.addEventListener('submit', async (event) =>
    {
        event.preventDefault();

        const submitButton = form.querySelector('[type="submit"]');

        if (submitButton)
        {
            submitButton.disabled = true;
        }

        const formData = new FormData(form);

        if (!formData.has('csrf_token'))
        {
            formData.append('csrf_token', window.csrfToken || '');
        }

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

if (!contentType.includes('application/json'))
{
    const text = await response.text();
    console.error('Réponse serveur HTML :', text);
    throw new Error('Réponse serveur non JSON');
}

            const data = await response.json();

            if (!response.ok || !data.success)
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
            console.error(error);
            showToast(error.message || 'Erreur serveur', 'error');
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