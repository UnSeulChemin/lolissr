// ==================================================
// Update Read Status
// ==================================================

import {
    showToast,
} from '../../core/toast.js';

import {
    debug,
    debugError,
} from '../../core/debug.js';

// ==================================================
// Selectors
// ==================================================

const BUTTON_SELECTOR =
    '.ajax-lu-button';

// ==================================================
// Helpers
// ==================================================

function getCsrfToken()
{
    return (
        document
            .querySelector(
                'meta[name="csrf-token"]',
            )
            ?.getAttribute(
                'content',
            )
        || ''
    );
}

function updateButtonState(
    button,
    readStatus,
)
{
    const isRead =
        Number(
            readStatus,
        ) === 1;

    button.dataset.readStatus =
        String(
            readStatus,
        );

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

    button.setAttribute(
        'aria-pressed',
        isRead
            ? 'true'
            : 'false',
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
        String(
            readStatus,
        ),
    );

    const csrfToken =
        getCsrfToken();

    if (csrfToken) {

        formData.append(
            'csrf_token',
            csrfToken,
        );
    }

    const response =
        await fetch(
            url,
            {
                method:
                    'POST',

                credentials:
                    'same-origin',

                headers:
                {
                    'X-Requested-With':
                        'XMLHttpRequest',

                    'Accept':
                        'application/json',
                },

                body:
                    formData,
            },
        );

    const data =
        await response.json();

    return {
        response,
        data,
    };
}

// ==================================================
// Init
// ==================================================

export function initUpdateReadStatus()
{
    // ==============================================
    // Prevent double init
    // ==============================================

    if (
        document.body.dataset
            .updateReadStatusInitialized
        === 'true'
    ) {
        return;
    }

    document.body.dataset
        .updateReadStatusInitialized =
            'true';

    // ==============================================
    // Click delegation
    // ==============================================

    document.addEventListener(
        'click',
        async (
            event,
        ) =>
        {
            const target =
                event.target;

            if (
                !(target instanceof Element)
            ) {
                return;
            }

            const button =
                target.closest(
                    BUTTON_SELECTOR,
                );

            if (
                !(
                    button
                    instanceof HTMLButtonElement
                )
            ) {
                return;
            }

            if (
                button.disabled
            ) {
                return;
            }

            const url =
                button.dataset.url;

            if (!url) {

                showToast(
                    'URL manquante',
                    'error',
                );

                return;
            }

            // ======================================
            // Current state
            // ======================================

            const currentReadStatus =
                Number(
                    button.dataset.readStatus
                    ?? 0,
                );

            const nextReadStatus =
                currentReadStatus === 1
                    ? 0
                    : 1;

            // ======================================
            // Disable
            // ======================================

            button.disabled =
                true;

            // ======================================
            // Optimistic UI
            // ======================================

            updateButtonState(
                button,
                nextReadStatus,
            );

            try {

                debug(
                    'READ_STATUS',
                    'update',
                    nextReadStatus,
                );

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
                        || 'Erreur lors de la mise à jour',
                    );
                }

                const finalStatus =
                    Number(
                        data.readStatus
                        ?? nextReadStatus,
                    );

                updateButtonState(
                    button,
                    finalStatus,
                );

                showToast(
                    data.message
                    || 'Mise à jour effectuée',
                    'success',
                );

            } catch (error) {

                debugError(
                    'READ_STATUS',
                    error,
                );

                // ==================================
                // Rollback
                // ==================================

                updateButtonState(
                    button,
                    currentReadStatus,
                );

                showToast(
                    error instanceof Error
                        ? error.message
                        : 'Erreur réseau',
                    'error',
                );

            } finally {

                button.disabled =
                    false;
            }
        },
    );

    // ==============================================
    // AJAX refresh
    // ==============================================

    document.addEventListener(
        'ajax:page-loaded',
        () =>
        {
            document
                .querySelectorAll(
                    BUTTON_SELECTOR,
                )
                .forEach(
                    (
                        button,
                    ) =>
                    {
                        if (
                            !(
                                button
                                instanceof HTMLButtonElement
                            )
                        ) {
                            return;
                        }

                        updateButtonState(
                            button,
                            Number(
                                button.dataset.readStatus
                                ?? 0,
                            ),
                        );
                    },
                );
        },
    );

    debug(
        'READ_STATUS',
        'initialized',
    );
}