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
    invalidatePage,
} from '../../router/page-invalidation.js';

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

    /*
    |--------------------------------------------------------------------------
    | URL
    |--------------------------------------------------------------------------
    */

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

    /*
    |--------------------------------------------------------------------------
    | CONFIRM
    |--------------------------------------------------------------------------
    */

    const confirmed =
        await deleteModal(
            'Supprimer ce manga ?',
        );

    if (!confirmed)
    {
        return;
    }

    /*
    |--------------------------------------------------------------------------
    | SAVE ORIGINAL TEXT
    |--------------------------------------------------------------------------
    */

    if (
        !button.dataset.originalText
    ) {

        button.dataset.originalText =
            button.textContent
            || 'Supprimer';
    }

    /*
    |--------------------------------------------------------------------------
    | LOADING
    |--------------------------------------------------------------------------
    */

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
                        Accept:
                            'application/json',
                    },
                },
            );

        /*
        |--------------------------------------------------------------------------
        | VALIDATION
        |--------------------------------------------------------------------------
        */

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

        /*
        |--------------------------------------------------------------------------
        | INVALIDATE
        |--------------------------------------------------------------------------
        */

        invalidatePage(
            '/',
        );

        /*
        |--------------------------------------------------------------------------
        | SUCCESS
        |--------------------------------------------------------------------------
        */

        showToast(
            data.message
            || 'Supprimé',
            'success',
        );

        /*
        |--------------------------------------------------------------------------
        | REDIRECT
        |--------------------------------------------------------------------------
        */

        const target =
            data.data?.redirect
            || redirectUrl;

        await navigateTo(
            target,
            {
                force:
                    true,
            },
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