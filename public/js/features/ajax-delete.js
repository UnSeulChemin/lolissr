import { showToast } from '../core/toast.js';

/**
 * Active la suppression AJAX d’un manga depuis la page détail.
 */
export function initMangaAjaxDelete()
{
    const deleteButton = document.querySelector('.js-delete-manga');

    if (!deleteButton)
    {
        return;
    }

    deleteButton.addEventListener('click', async () =>
    {
        const confirmed = window.confirm(
            'Supprimer ce manga ? Cette action est irréversible.'
        );

        if (!confirmed)
        {
            return;
        }

        const url = deleteButton.dataset.url;
        const redirectUrl = deleteButton.dataset.redirect;
        const originalText = deleteButton.textContent;

        if (!url)
        {
            showToast('URL de suppression introuvable.', 'error');
            return;
        }

        const formData = new FormData();
        formData.append('csrf_token', window.csrfToken || '');

        deleteButton.disabled = true;
        deleteButton.textContent = 'Suppression...';

        try
        {
            const response = await fetch(url, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (!response.ok || !data.success)
            {
                showToast(
                    data.message || 'Erreur lors de la suppression.',
                    'error'
                );

                deleteButton.disabled = false;
                deleteButton.textContent = originalText;
                return;
            }

            showToast(
                data.message || 'Manga supprimé avec succès.',
                'success'
            );

            window.location.href = data.redirect || redirectUrl || '/';
        }
        catch (error)
        {
            showToast('Erreur réseau lors de la suppression.', 'error');
            deleteButton.disabled = false;
            deleteButton.textContent = originalText;
        }
    });
}