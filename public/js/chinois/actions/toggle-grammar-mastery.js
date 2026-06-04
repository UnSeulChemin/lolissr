// =========================================
// TOGGLE GRAMMAR MASTERY
// =========================================

import {
    invalidateRoute,
} from '../../router/route-invalidation.js';

import {
    invalidatePrefetch,
} from '../../router/prefetch/prefetch-cache.js';

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
} from '../../core/debug/debug.js';

// =========================================
// STATE
// =========================================

let initialized =
    false;

// =========================================
// HELPERS
// =========================================

function isValidButton(
    button,
)
{
    return (
        button
        instanceof HTMLButtonElement
    );
}

function setLoading(
    button,
    state,
)
{
    if (state)
    {
        button.dataset.loading =
            '1';

        button.disabled =
            true;

        return;
    }

    delete button.dataset.loading;

    button.disabled =
        false;
}

function updateButtonState(
    button,
    mastered,
)
{
    button.dataset.maitrise =
        mastered
            ? '1'
            : '0';

    button.classList.toggle(
        'active',
        mastered,
    );

    button.setAttribute(
        'aria-pressed',
        mastered
            ? 'true'
            : 'false',
    );

    const label =
        mastered
            ? 'Retirer la maîtrise'
            : 'Marquer comme maîtrisé';

    button.title =
        label;

    button.setAttribute(
        'aria-label',
        label,
    );
}

// =========================================
// TOGGLE
// =========================================

async function toggleMastery(
    button,
)
{
    const url =
        button.dataset.url;

    if (!url)
    {
        showToast(
            'URL manquante',
            'error',
        );

        return;
    }

    setLoading(
        button,
        true,
    );

    try {

        const data =
            await post(
                url,
                {
                    id:
                        button.dataset.id
                        ?? '',
                },
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

            showToast(
                data?.message
                    ?? 'Erreur lors de la mise à jour',
                'error',
            );

            return;
        }

        const mastered =
            Number(
                data.data?.maitrise
                    ?? 0,
            ) === 1;

        updateButtonState(
            button,
            mastered,
        );

        invalidateRoute(
            window.location.href,
        );

        invalidatePrefetch(
            window.location.href,
        );

        showToast(
            data.message
                ?? 'Mise à jour effectuée',
            'success',
        );

    } catch (error) {

        if (
            error instanceof Error
            && error.name
                === 'AbortError'
        ) {
            return;
        }

        debugError(
            'ToggleGrammarMastery',
            error,
        );

        showToast(
            'Erreur réseau',
            'error',
        );

    } finally {

        setLoading(
            button,
            false,
        );
    }
}

// =========================================
// INIT
// =========================================

export function initToggleGrammaireMaitrise()
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
        '.grammar-ajax',
        (
            _,
            button,
        ) =>
        {
            if (
                !isValidButton(
                    button,
                )
            ) {
                return;
            }

            if (
                button.dataset.loading
                === '1'
            ) {
                return;
            }

            void toggleMastery(
                button,
            );
        },
    );

    debug(
        'GRAMMAR',
        'initialized',
    );
}