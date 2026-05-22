import { showToast } from '../../core/toast.js';

function getCsrfToken()
{
    return document
        .querySelector(
            'meta[name="csrf-token"]'
        )
        ?.getAttribute('content')
        ?? '';
}

function updateButtonState(
    button,
    readStatus,
)
{
    button.dataset.readStatus =
        String(readStatus);

    button.classList.toggle(
        'active',
        readStatus === 1,
    );

    const label =
        readStatus === 1
            ? 'Marquer comme non lu'
            : 'Marquer comme lu';

    button.title = label;

    button.setAttribute(
        'aria-label',
        label,
    );
}

async function sendReadStatusRequest(
    url,
    readStatus,
)
{
    const formData =
        new FormData();

    formData.append(
        'readStatus',
        String(readStatus),
    );

    const csrfToken =
        getCsrfToken();

    if (csrfToken !== '')
    {
        formData.append(
            'csrf_token',
            csrfToken,
        );
    }

    const response =
        await fetch(url, {
            method: 'POST',

            headers:
            {
                'X-Requested-With':
                    'XMLHttpRequest',

                'Accept':
                    'application/json',
            },

            body: formData,
        });

    const data =
        await response.json();

    return {
        response,
        data,
    };
}

export function initUpdateReadStatus()
{
    document.addEventListener(
        'click',
        async (event) =>
        {
            const button =
                event.target.closest(
                    '.ajax-lu-button',
                );

            if (
                !button
                || button.disabled
            )
            {
                return;
            }

            const url =
                button.dataset.url;

            if (!url)
            {
                showToast(
                    'URL de mise à jour manquante',
                    'error',
                );

                return;
            }

            const currentReadStatus =
                button.dataset
                    .readStatus === '1';

            const nextReadStatus =
                currentReadStatus
                    ? 0
                    : 1;

            button.disabled = true;

            try
            {
                const {
                    response,
                    data,
                } =
                    await sendReadStatusRequest(
                        url,
                        nextReadStatus,
                    );

                if (
                    !response.ok
                    || !data.success
                )
                {
                    showToast(
                        data.message
                            ?? 'Erreur lors de la mise à jour',
                        'error',
                    );

                    return;
                }

                const readStatus =
                    Number(
                        data.readStatus,
                    );

                updateButtonState(
                    button,
                    readStatus,
                );

                showToast(
                    data.message
                        ?? 'Statut mis à jour',
                    'success',
                );
            }
            catch
            {
                showToast(
                    'Erreur réseau',
                    'error',
                );
            }
            finally
            {
                button.disabled = false;
            }
        },
    );
}