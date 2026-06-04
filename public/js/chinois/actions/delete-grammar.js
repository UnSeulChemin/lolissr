// =========================================
// DELETE GRAMMAIRE
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
    invalidateRoute,
} from '../../router/route-invalidation.js';

import {
    invalidatePrefetch,
} from '../../router/prefetch/prefetch-cache.js';

// =========================================
// STATE
// =========================================

let initialized =
    false;

// =========================================
// DELETE
// =========================================

async function deleteGrammaire(
    button,
)
{
    if (
        button.disabled
    ) {

        return;
    }

    const id =
        Number(
            button.dataset.id,
        );

    const url =
        button.dataset.url;

    const item =
        button.closest(
            '.grammar-item',
        );

    /*
    |--------------------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------------------
    */

    if (
        !url
        || id <= 0
    ) {

        handleError(
            new FrontendError(
                'Paramètres invalides',
                {
                    code:
                        'INVALID_GRAMMAR_DELETE',
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
        window.confirm(
            'Supprimer cette règle de grammaire ?',
        );

    if (!confirmed)
    {

        return;
    }

    /*
    |--------------------------------------------------------------------------
    | LOADING
    |--------------------------------------------------------------------------
    */

    button.disabled =
        true;

    try {

        debug(
            'GRAMMAIRE_DELETE',
            'request',
            {
                id,
            },
        );

        const data =
            await post(
                url,
                {
                    id,
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
                        'DELETE_GRAMMAR_FAILED',
                },
            );
        }

        /*
        |--------------------------------------------------------------------------
        | REMOVE CARD
        |--------------------------------------------------------------------------
        */

        item?.remove();

        invalidateRoute(
            window.location.href,
        );

        invalidatePrefetch(
            window.location.href,
        );

        /*
        |--------------------------------------------------------------------------
        | SUCCESS
        |--------------------------------------------------------------------------
        */

        showToast(
            data.message
            || 'Grammaire supprimée',
            'success',
        );

    } catch (error) {

        button.disabled =
            false;

        handleError(
            error,
        );
    }
}

// =========================================
// INIT
// =========================================

export function initDeleteGrammaire()
{
    if (initialized)
    {

        return;
    }

    initialized =
        true;

    delegate(
        document,
        'click',
        '.grammaire-delete',
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

            void deleteGrammaire(
                button,
            );
        },
    );

    debug(
        'GRAMMAIRE_DELETE',
        'initialized',
    );
}