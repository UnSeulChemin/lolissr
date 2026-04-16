import { showToast } from '../toast.js';

const livreInput = document.getElementById('livre');
const slugInput = document.getElementById('slug');
const imageInput = document.getElementById('image');
const uploadText = document.querySelector('.form-upload-text');
const form = document.querySelector('.form-layout');

if (livreInput && slugInput)
{
    livreInput.addEventListener('input', function ()
    {
        const slug = this.value
            .toLowerCase()
            .trim()
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-')
            .replace(/^-|-$/g, '');

        slugInput.value = slug;
    });
}

if (imageInput && uploadText)
{
    imageInput.addEventListener('change', function ()
    {
        if (this.files.length > 0)
        {
            uploadText.textContent = this.files[0].name;
        }
        else
        {
            uploadText.textContent = 'Choisir une image';
        }
    });
}

if (form)
{
    form.addEventListener('submit', async function (e)
    {
        e.preventDefault();

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

            if (!contentType.includes('application/json'))
            {
                throw new Error('Réponse non JSON');
            }

            const data = await response.json();

            if (data.success)
            {
                showToast(data.message || 'Manga ajouté avec succès', 'success');
                form.reset();

                if (uploadText)
                {
                    uploadText.textContent = 'Choisir une image';
                }
            }
            else
            {
                showToast(data.message || 'Une erreur est survenue', 'error');
            }
        }
        catch (error)
        {
            console.error(error);
            showToast('Erreur serveur', 'error');
        }
    });
}