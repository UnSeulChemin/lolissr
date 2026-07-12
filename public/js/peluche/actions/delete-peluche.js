// =========================================
// DELETE PELUCHE
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
} from '../../core/debug/debug.js';

import {
    handleError,
} from '../../core/errors/error-handler.js';

import {
    FrontendError,
} from '../../core/errors/FrontendError.js';

import {
    navigateTo,
} from '../../router/router-navigation.js';

import {
    invalidatePeluchePages,
} from '../peluche-cache.js';

import {
    deleteModal,
} from '../../core/modal/modal.js';

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
// DELETE PELUCHE
// =========================================

async function deletePeluche(
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

        handleError(
            new FrontendError(
                'URL invalide',
                {
                    code:
                        'INVALID_DELETE_URL',
                },
            ),
        );

        return;
    }

    const confirmed =
        await deleteModal(
            'Supprimer cette peluche ?',
        );

    if (!confirmed)
    {
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
            'DELETE_PELUCHE',
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
                        Accept:
                            'application/json',
                    },
                },
            );

        if (
            data?.success
            !== true
        ) {

            throw new FrontendError(
                data?.message
                || 'Erreur suppression',
                {
                    code:
                        'DELETE_FAILED',
                },
            );
        }

        const target =
            data.data?.redirect
            || redirectUrl;

        invalidatePeluchePages();

        showToast(
            data.message
            || 'Peluche supprimée',
            'success',
        );

        await navigateTo(
            target,
        );

    } catch (error) {

        handleError(
            error,
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

export function initDeletePeluche()
{
    if (initialized) {

        return;
    }

    initialized =
        true;

    delegate(
        document,
        'click',
        '.js-delete-peluche',
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

            void deletePeluche(
                button,
            );
        },
    );

    debug(
        'DELETE_PELUCHE',
        'initialized',
    );
}