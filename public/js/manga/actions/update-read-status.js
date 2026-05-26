// =========================================
// UPDATE READ STATUS
// =========================================

import {
    post,
} from '../../core/http.js';

import {
    $$,
    delegate,
} from '../../core/dom.js';

import {
    showToast,
} from '../../core/toast.js';

import {
    debug,
    debugError,
} from '../../core/debug.js';

// =========================================
// Selectors
// =========================================

const BUTTON_SELECTOR =
    '.ajax-lu-button';

// =========================================
// Helpers
// =========================================

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

// =========================================
// Refresh
// =========================================

function refreshButtons()
{
    $$(
        BUTTON_SELECTOR,
    ).forEach(
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
}

// =========================================
// API
// =========================================

async function updateReadStatus(
    button,
)
{
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

    // =====================================
    // Current State
    // =====================================

    const currentReadStatus =
        Number(
            button.dataset.readStatus
            ?? 0,
        );

    const nextReadStatus =
        currentReadStatus === 1
            ? 0
            : 1;

    // =====================================
    // Disable
    // =====================================

    button.disabled =
        true;

    // =====================================
    // Optimistic UI
    // =====================================

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

        const data =
            await post(
                url,
                {
                    readStatus:
                        nextReadStatus,
                },
                {
                    headers:
                    {
                        'Accept':
                            'application/json',
                    },
                },
            );

        debug(
            'READ_STATUS',
            'response',
            data,
        );

        // =================================
        // Validation
        // =================================

        if (
            data?.success !== true
        ) {

            throw new Error(
                data?.message
                || 'Erreur lors de la mise à jour',
            );
        }

        // =================================
        // Backend Data
        // =================================

        const finalStatus =
            Number(
                data?.data?.readStatus
                ?? nextReadStatus,
            );

        // =================================
        // Sync UI
        // =================================

        updateButtonState(
            button,
            finalStatus,
        );

        showToast(
            data?.message
            || 'Mise à jour effectuée',
            'success',
        );

    } catch (error) {

        debugError(
            'READ_STATUS',
            error,
        );

        // =================================
        // Rollback
        // =================================

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
}

// =========================================
// Init
// =========================================

export function initUpdateReadStatus()
{
    // =====================================
    // Prevent Double Init
    // =====================================

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

    // =====================================
    // Click
    // =====================================

    delegate(
        document,
        'click',
        BUTTON_SELECTOR,
        (
            _,
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

            void updateReadStatus(
                button,
            );
        },
    );

    // =====================================
    // AJAX Refresh
    // =====================================

    document.addEventListener(
        'ajax:page-loaded',
        refreshButtons,
    );

    refreshButtons();

    debug(
        'READ_STATUS',
        'initialized',
    );
}