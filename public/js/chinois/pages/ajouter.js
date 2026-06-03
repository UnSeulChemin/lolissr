// =========================================
// AJOUTER CHINOIS
// =========================================

import {
    request,
} from '../../core/http.js';

import {
    $,
} from '../../core/dom.js';

import {
    showToast,
} from '../../core/toast.js';

import {
    debug,
    debugError,
} from '../../core/debug/debug.js';

// =========================================
// CONFIG
// =========================================

const FORM_SELECTOR =
    '.form-layout[data-form-page]';

// =========================================
// INIT
// =========================================

export function initAjouterPage()
{
    const form =
        $(FORM_SELECTOR);

    if (
        !(
            form
            instanceof HTMLFormElement
        )
    ) {
        return;
    }

    if (
        form.dataset.initialized
        === 'true'
    ) {
        return;
    }

    form.dataset.initialized =
        'true';

    form.addEventListener(
        'submit',
        async (
            event,
        ) =>
        {
            event.preventDefault();

            const submitButton =
                form.querySelector(
                    '[type="submit"]',
                );

            if (
                submitButton
                instanceof HTMLButtonElement
            ) {

                submitButton.disabled =
                    true;
            }

            try {

                debug(
                    'CHINOIS',
                    'submit-start',
                );

                const data =
                    await request(
                        form.action,
                        {
                            method:
                                'POST',

                            body:
                                new FormData(
                                    form,
                                ),
                        },
                    );

                debug(
                    'CHINOIS',
                    'response',
                    data,
                );

                showToast(
                    data?.message
                    || 'Ajout effectué',
                    'success',
                );

                form.reset();

                debug(
                    'CHINOIS',
                    'success',
                );

            } catch (error) {

                debugError(
                    'CHINOIS',
                    error,
                );

                const errors =
                    error?.details
                        ?.data
                        ?.errors;

                if (
                    errors
                    && Object.keys(errors).length
                ) {

                    showToast(
                        Object.values(
                            errors,
                        )[0],
                        'error',
                    );

                    return;
                }

                showToast(
                    error?.message
                    || 'Erreur serveur',
                    'error',
                );

            } finally {

                if (
                    submitButton
                    instanceof HTMLButtonElement
                ) {

                    submitButton.disabled =
                        false;
                }

                debug(
                    'CHINOIS',
                    'submit-end',
                );
            }
        },
    );

    debug(
        'CHINOIS',
        'initialized',
    );
}