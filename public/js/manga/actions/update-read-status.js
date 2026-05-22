import { showToast }
    from '../../core/toast.js';

/*
|------------------------------------------------------------------
| CSRF
|------------------------------------------------------------------
*/

function getCsrfToken()
{
    return document
        .querySelector(
            'meta[name="csrf-token"]',
        )
        ?.getAttribute(
            'content',
        )
        ?? '';
}

/*
|------------------------------------------------------------------
| UI
|------------------------------------------------------------------
*/

function updateButtonState(
    button,
    readStatus,
)
{
    const isRead =
        readStatus === 1;

    button.dataset.readStatus =
        String(readStatus);

    button.classList.toggle(
        'active',
        isRead,
    );

    const label =
        isRead
            ? 'Marquer comme non lu'
            : 'Marquer comme lu';

    button.title =
        label;

    button.setAttribute(
        'aria-label',
        label,
    );
}

/*
|------------------------------------------------------------------
| Request
|------------------------------------------------------------------
*/

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

    if (csrfToken !== '') {
        formData.append(
            'csrf_token',
            csrfToken,
        );
    }

    const response =
        await fetch(
            url,
            {
                method: 'POST',

                headers:
                {
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

    return {
        response,
        data,
    };
}

/*
|------------------------------------------------------------------
| Init
|------------------------------------------------------------------
*/

export function initUpdateReadStatus()
{
    /*
    |--------------------------------------------------------------
    | Anti double init
    |--------------------------------------------------------------
    */

    if (
        document.body.dataset
            .updateReadStatusInit
        === 'true'
    ) {
        return;
    }

    document.body.dataset
        .updateReadStatusInit =
            'true';

    /*
    |--------------------------------------------------------------
    | Delegation globale
    |--------------------------------------------------------------
    */

    document.addEventListener(
        'click',
        async event =>
        {
            const button =
                event.target.closest(
                    '.ajax-lu-button',
                );

            if (!button) {
                return;
            }

            if (button.disabled) {
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

            /*
            |------------------------------------------------------
            | Current state
            |------------------------------------------------------
            */

            const currentReadStatus =
                button.dataset.readStatus
                === '1';

            const nextReadStatus =
                currentReadStatus
                    ? 0
                    : 1;

            /*
            |------------------------------------------------------
            | Disable button
            |------------------------------------------------------
            */

            button.disabled =
                true;

            /*
            |------------------------------------------------------
            | Optimistic UI
            |------------------------------------------------------
            */

            updateButtonState(
                button,
                nextReadStatus,
            );

            try {

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
                ) {
                    throw new Error(
                        data.message
                        ?? 'Erreur lors de la mise à jour',
                    );
                }

                /*
                |--------------------------------------------------
                | Final server state
                |--------------------------------------------------
                */

                const readStatus =
                    Number(
                        data.readStatus
                        ?? nextReadStatus,
                    );

                updateButtonState(
                    button,
                    readStatus,
                );

                /*
                |--------------------------------------------------
                | Toast
                |--------------------------------------------------
                */

                showToast(
                    data.message
                    ?? 'Statut mis à jour',
                    'success',
                );

            } catch (error) {

                console.error(
                    error,
                );

                /*
                |--------------------------------------------------
                | Rollback
                |--------------------------------------------------
                */

                updateButtonState(
                    button,
                    currentReadStatus
                        ? 1
                        : 0,
                );

                showToast(
                    error?.message
                    ?? 'Erreur réseau',
                    'error',
                );

            } finally {

                button.disabled =
                    false;
            }
        },
    );

    /*
    |--------------------------------------------------------------
    | Re-sync after AJAX
    |--------------------------------------------------------------
    */

    document.addEventListener(
        'ajax:series-loaded',
        () =>
        {
            document
                .querySelectorAll(
                    '.ajax-lu-button',
                )
                .forEach(
                    button =>
                    {
                        const readStatus =
                            Number(
                                button.dataset.readStatus
                                ?? 0,
                            );

                        updateButtonState(
                            button,
                            readStatus,
                        );
                    },
                );
        },
    );
}