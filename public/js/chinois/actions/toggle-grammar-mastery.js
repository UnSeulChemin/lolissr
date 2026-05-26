// ==================================================
// Toggle Grammar Mastery
// ==================================================

import {
    showToast,
} from '../../core/toast.js';

import {
    debugError,
} from '../../core/debug.js';

// ==================================================
// State
// ==================================================

let initialized =
    false;

// ==================================================
// Helpers
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
    if (state) {

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

async function toggleMastery(
    button,
)
{
    const url =
        button.dataset.url;

    if (!url) {

        showToast(
            'URL manquante',
            'error',
        );

        return;
    }

    const formData =
        new FormData();

    formData.append(
        'id',
        button.dataset.id
            ?? '',
    );

    const csrfToken =
        getCsrfToken();

    if (csrfToken) {

        formData.append(
            'csrf_token',
            csrfToken,
        );
    }

    setLoading(
        button,
        true,
    );

    try {

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

        if (
            !response.ok
            || !data.success
        ) {

            showToast(
                data.message
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

// ==================================================
// Events
// ==================================================

function handleClick(
    event,
)
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
            '.grammar-mastered',
        );

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
}

// ==================================================
// Init
// ==================================================

export function initToggleGrammaireMaitrise()
{
    if (initialized) {
        return;
    }

    initialized =
        true;

    document.addEventListener(
        'click',
        handleClick,
    );
}