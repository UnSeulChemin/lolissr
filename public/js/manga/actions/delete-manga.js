// =========================================
// DELETE MANGA
// =========================================

import {
    post,
} from '../../core/http.js';

import {
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
// State
// =========================================

let initialized =
    false;

// =========================================
// UI
// =========================================

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

// =========================================
// Delete
// =========================================

async function deleteManga(
    button,
)
{
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

    // =====================================
    // Prevent double click
    // =====================================

    if (
        button.disabled
    ) {
        return;
    }

    // =====================================
    // Confirm
    // =====================================

    const confirmed =
        window.confirm(
            'Supprimer ce manga ?\nCette action est irréversible.',
        );

    if (
        !confirmed
    ) {
        return;
    }

    // =====================================
    // Store label
    // =====================================

    if (
        !button.dataset.originalText
    ) {

        button.dataset.originalText =
            button.textContent
            || 'Supprimer';
    }

    // =====================================
    // Loading
    // =====================================

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

        const data =
            await post(
                url,
                {},
                {
                    headers:
                    {
                        'X-Partial':
                            'true',

                        'Accept':
                            'application/json',
                    },
                },
            );

        // =================================
        // Server Error
        // =================================

        if (
            !data?.success
        ) {

            throw new Error(
                data?.message
                ?? 'Erreur lors de la suppression.',
            );
        }

        // =================================
        // Success
        // =================================

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

        // =================================
        // Redirect
        // =================================

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

        // =================================
        // Restore
        // =================================

        setLoadingState(
            button,
            false,
        );
    }
}

// =========================================
// Init
// =========================================

export function initDeleteManga()
{
    if (initialized) {
        return;
    }

    initialized =
        true;

    delegate(
        document,
        'click',
        '.js-delete-manga',
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

            void deleteManga(
                button,
            );
        },
    );

    debug(
        'DELETE',
        'initialized',
    );
}