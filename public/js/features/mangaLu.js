import { showToast } from '../core/toast.js';

function getCsrfToken()
{
    return document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute('content') ?? '';
}

export function initMangaLuToggle()
{
    document.addEventListener('click', async (event) =>
    {
        const button = event.target.closest('.ajax-lu-button');

        if (!button || button.disabled) {
            return;
        }

        const url = button.dataset.url;

        if (!url) {
            showToast('URL de mise à jour manquante', 'error');
            return;
        }

        const currentLu = button.dataset.lu === '1';
        const nextLu = currentLu ? 0 : 1;

        const formData = new FormData();
        formData.append('lu', String(nextLu));

        const csrfToken = getCsrfToken();

        if (csrfToken !== '') {
            formData.append('csrf_token', csrfToken);
        }

        button.disabled = true;

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: formData
            });

            const data = await response.json();

            if (!response.ok || !data.success) {
                showToast(data.message ?? 'Erreur lors de la mise à jour', 'error');
                return;
            }

            const lu = Number(data.lu);

            button.dataset.lu = String(lu);
            button.classList.toggle('active', lu === 1);

            button.title = lu === 1
                ? 'Marquer comme non lu'
                : 'Marquer comme lu';

            button.setAttribute(
                'aria-label',
                lu === 1 ? 'Marquer comme non lu' : 'Marquer comme lu'
            );

            showToast(data.message ?? 'Statut mis à jour', 'success');
        } catch {
            showToast('Erreur réseau', 'error');
        } finally {
            button.disabled = false;
        }
    });
}