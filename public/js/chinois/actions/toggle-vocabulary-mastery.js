// =========================================
// TOGGLE VOCABULAIRE MASTERY
// =========================================

import {
    invalidatePages,
} from '../../router/page-utils.js';

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
            Boolean(
                data?.data?.maitrise,
            );

        updateButtonState(
            button,
            mastered,
        );

        invalidatePages(
            window.location.href,
            `${window.baseUri}chinois/flashcards/vocabulaire`,
        );

        let message =
            data.message
            ?? 'Mise à jour effectuée';

        if (data?.data?.xpEarned)
        {
            message += ' ⭐ +5 XP';
        }

        showToast(
            message,
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
            'ToggleVocabulaireMastery',
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

export function initToggleVocabulaireMaitrise()
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
        '.vocabulary-ajax',
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
        'VOCABULAIRE',
        'initialized',
    );
}