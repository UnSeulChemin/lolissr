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

import {
    navigateTo,
} from '../../navigation/ajax-navigation.js';

// =========================================
// STATE
// =========================================

let initialized =
    false;

// =========================================
// UI
// =========================================

function setLoadingState(
    button,
    loading,
)
{
    button.disabled =
        loading;

    button.textContent =
        loading
            ? 'Suppression...'
            : (
                button.dataset.originalText
                || 'Supprimer'
            );
}

// =========================================
// DELETE
// =========================================

async function deleteManga(
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

    const redirectUrl =
        button.dataset.redirect
        || '/';

    if (!url) {

        showToast(
            'URL invalide',
            'error',
        );

        return;
    }

    const confirmed =
        window.confirm(
            'Supprimer ce manga ?',
        );

    if (!confirmed) {
        return;
    }

    if (
        !button.dataset.originalText
    ) {

        button.dataset.originalText =
            button.textContent
            || 'Supprimer';
    }

    setLoadingState(
        button,
        true,
    );

    try {

        debug(
            'DELETE',
            'request',
            url,
        );

        const data =
            await post(
                url,
                {},
                {
                    headers:
                    {
                        'X-Partial':
                            'true',

                        Accept:
                            'application/json',
                    },
                },
            );

        if (
            !data?.success
        ) {

            throw new Error(
                data?.message
                || 'Erreur suppression',
            );
        }

        showToast(
            data.message
            || 'Supprimé',
            'success',
        );

        const target =
            data.data?.redirect
            || redirectUrl;

        await navigateTo(
            target,
        );

    } catch (error) {

        debugError(
            'DELETE',
            error,
        );

        showToast(
            error?.message
            || 'Erreur réseau',
            'error',
        );

        setLoadingState(
            button,
            false,
        );
    }
}

// =========================================
// INIT
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