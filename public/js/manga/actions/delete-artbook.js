// =========================================
// DELETE ARTBOOK
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
    invalidateMangaPages,
} from '../manga-cache.js';

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
// DELETE ARTBOOK
// =========================================

async function deleteArtbook(
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

    if (! url)
    {
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
            'Supprimer cet artbook ?',
        );

    if (! confirmed)
    {
        return;
    }

    if (
        ! button.dataset.originalText
    ) {
        button.dataset.originalText =
            button.textContent
            || 'Supprimer';
    }

    setLoadingState(
        button,
        true,
    );

    try
    {
        debug(
            'DELETE_ARTBOOK',
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
                        'DELETE_ARTBOOK_FAILED',
                },
            );
        }

        const target =
            data.data?.redirect
            || redirectUrl;

        invalidateMangaPages();

        showToast(
            data.message
            || 'Artbook supprimé',
            'success',
        );

        await navigateTo(
            target,
        );
    }
    catch (error)
    {
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

export function initDeleteArtbook()
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
        '.js-delete-artbook',
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

            void deleteArtbook(
                button,
            );
        },
    );

    debug(
        'DELETE_ARTBOOK',
        'initialized',
    );
}