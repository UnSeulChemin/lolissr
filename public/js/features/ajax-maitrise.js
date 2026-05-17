import { showToast } from '../core/toast.js';

function getCsrfToken()
{
    return document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute('content') ?? '';
}

export function initGrammaireMaitrise()
{
    document.addEventListener('click', async (event) =>
    {
        const button = event.target.closest('.grammar-mastered');

        if (!button || button.disabled)
        {
            return;
        }

        const url = button.dataset.url;

        if (!url)
        {
            showToast('URL manquante', 'error');

            return;
        }

        const current = button.dataset.maitrise === '1';
        const next = current ? 0 : 1;

        const formData = new FormData();

        formData.append('id', button.dataset.id);
        formData.append('maitrise', String(next));

        const csrfToken = getCsrfToken();

        if (csrfToken !== '')
        {
            formData.append('csrf_token', csrfToken);
        }

        button.disabled = true;

        try
        {
            const response = await fetch(url,
            {
                method: 'POST',

                headers:
                {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },

                body: formData
            });

            const data = await response.json();

            if (!response.ok || !data.success)
            {
                showToast(
                    data.message ?? 'Erreur lors de la mise à jour',
                    'error'
                );

                return;
            }

            const maitrise = Number(data.maitrise);

            button.dataset.maitrise = String(maitrise);

            button.classList.toggle(
                'active',
                maitrise === 1
            );
        }
        catch
        {
            showToast('Erreur réseau', 'error');
        }
        finally
        {
            button.disabled = false;
        }
    });
}