import {
    showToast,
} from '../../core/toast.js';

function getCsrfToken()
{
    return document
        .querySelector(
            'meta[name="csrf-token"]',
        )
        ?.getAttribute('content')
        ?? '';
}

export function initToggleGrammaireMaitrise()
{
    document.addEventListener(
        'click',
        async (event) =>
        {
            const target =
                event.target;

            if (!(target instanceof Element)) {
                return;
            }

            const button =
                target.closest(
                    '.grammar-mastered',
                );

            if (!button) {
                return;
            }

            if (
                button.dataset.loading === '1'
            ) {
                return;
            }

            const url =
                button.dataset.url;

            if (!url) {

                showToast(
                    'URL de mise à jour manquante',
                    'error',
                );

                return;
            }

            button.dataset.loading = '1';

            const formData =
                new FormData();

            formData.append(
                'id',
                button.dataset.id ?? '',
            );

            const csrfToken =
                getCsrfToken();

            if (csrfToken !== '') {

                formData.append(
                    'csrf_token',
                    csrfToken,
                );
            }

            try {

                const response =
                    await fetch(
                        url,
                        {
                            method: 'POST',

                            headers: {
                                'X-Requested-With':
                                    'XMLHttpRequest',

                                'Accept':
                                    'application/json',
                            },

                            body: formData,
                        },
                    );

                const data =
                    await response.json();

                if (
                    !response.ok
                    || !data.success
                ) {

                    showToast(
                        data.message
                            ?? 'Erreur lors de la mise à jour',
                        'error',
                    );

                    return;
                }

                const mastered =
                    Number(
                        data.data?.maitrise ?? 0,
                    ) === 1;

                button.dataset.maitrise =
                    mastered
                        ? '1'
                        : '0';

                button.classList.toggle(
                    'active',
                    mastered,
                );

                button.setAttribute(
                    'aria-pressed',
                    mastered
                        ? 'true'
                        : 'false',
                );

                const label =
                    mastered
                        ? 'Retirer la maîtrise'
                        : 'Marquer comme maîtrisé';

                button.title =
                    label;

                button.setAttribute(
                    'aria-label',
                    label,
                );

                showToast(
                    data.message
                        ?? 'Mise à jour effectuée',
                    'success',
                );

            } catch {

                showToast(
                    'Erreur réseau',
                    'error',
                );

            } finally {

                delete button.dataset.loading;
            }
        },
    );
}