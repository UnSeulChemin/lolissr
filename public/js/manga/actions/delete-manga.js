// ==================================================
// Delete Manga
// ==================================================

import {
    showToast,
} from '../../core/toast.js';

import {
    debug,
    debugError,
} from '../../core/debug.js';

// ==================================================
// State
// ==================================================

let initialized =
    false;

// ==================================================
// CSRF
// ==================================================

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

// ==================================================
// Request
// ==================================================

async function sendDeleteRequest(
    url,
)
{
    const formData =
        new FormData();

    const csrfToken =
        getCsrfToken();

    if (
        csrfToken !== ''
    ) {

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

                    'X-Partial':
                        'true',

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
// UI
// ==================================================

function setLoadingState(
    button,
    isLoading,
)
{
    button.disabled =
        isLoading;

    button.textContent =
        isLoading
            ? 'Suppression...'
            : (
                button.dataset.originalText
                || 'Supprimer'
            );
}

// ==================================================
// Init
// ==================================================

export function initDeleteManga()
{
    if (initialized) {
        return;
    }

    initialized =
        true;

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
                    '.js-delete-manga',
                );

            if (
                !button
                || !(
                    button
                    instanceof HTMLButtonElement
                )
            ) {
                return;
            }

            // ======================================
            // Prevent double click
            // ======================================

            if (
                button.disabled
            ) {
                return;
            }

            // ======================================
            // Confirm
            // ======================================

            const confirmed =
                window.confirm(
                    'Supprimer ce manga ?\nCette action est irréversible.',
                );

            if (
                !confirmed
            ) {
                return;
            }

            // ======================================
            // Data
            // ======================================

            const url =
                button.dataset.url;

            const redirectUrl =
                button.dataset.redirect
                || '/';

            if (!url) {

                showToast(
                    'URL de suppression introuvable.',
                    'error',
                );

                return;
            }

            // ======================================
            // Store original label
            // ======================================

            if (
                !button.dataset.originalText
            ) {

                button.dataset.originalText =
                    button.textContent
                    || 'Supprimer';
            }

            // ======================================
            // Loading
            // ======================================

            setLoadingState(
                button,
                true,
            );

            debug(
                'DELETE',
                'request',
                url,
            );

            try {

                const {
                    response,
                    data,
                } =
                    await sendDeleteRequest(
                        url,
                    );

                // ==================================
                // Server error
                // ==================================

                if (
                    !response.ok
                    || !data.success
                ) {

                    throw new Error(
                        data.message
                        ?? 'Erreur lors de la suppression.',
                    );
                }

                // ==================================
                // Success
                // ==================================

                showToast(
                    data.message
                    ?? 'Manga supprimé avec succès.',
                    'success',
                );

                debug(
                    'DELETE',
                    'success',
                    url,
                );

                // ==================================
                // Redirect
                // ==================================

                window.location.href =
                    data.data?.redirect
                    || redirectUrl;

            } catch (error) {

                debugError(
                    'DELETE',
                    error,
                );

                showToast(
                    error instanceof Error
                        ? error.message
                        : 'Erreur réseau lors de la suppression.',
                    'error',
                );

                // ==================================
                // Restore
                // ==================================

                setLoadingState(
                    button,
                    false,
                );
            }
        },
    );

    debug(
        'DELETE',
        'initialized',
    );
}