// =========================================
// DELETE VOCABULAIRE
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
// DELETE
// =========================================

async function deleteVocabulaire(
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
            '.chinois-vocab-card',
        );

    if (
        !url
        || id <= 0
    ) {
        handleError(
            new FrontendError(
                'Paramètres invalides',
                {
                    code:
                        'INVALID_VOCAB_DELETE',
                },
            ),
        );

        return;
    }

    const confirmed =
        await deleteModal(
            'Supprimer ce vocabulaire ?',
        );

    if (!confirmed)
    {
        return;
    }

    button.disabled =
        true;

    try {

        debug(
            'VOCABULAIRE_DELETE',
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
                        'DELETE_VOCAB_FAILED',
                },
            );
        }

    const isFlashcard =
        document.getElementById(
            'flashcard-counter',
        ) !== null;

    if (!isFlashcard)
    {
        item?.remove();
    }
    else
    {
        location.reload();

        return;
    }

    invalidatePage(
        window.location.href,
    );

    showToast(
        data.message
        || 'Vocabulaire supprimé',
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

export function initDeleteVocabulaire()
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
        '.vocabulaire-delete',
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

            if (
                !button
                    .closest(
                        '.chinois-vocab-card',
                    )
            ) {
                return;
            }

            void deleteVocabulaire(
                button,
            );
        },
    );

    debug(
        'VOCABULAIRE_DELETE',
        'initialized',
    );
}