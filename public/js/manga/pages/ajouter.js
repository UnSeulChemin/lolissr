// =========================================
// AJOUTER PAGE
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

import {
    generateSlug,
} from '../utils/slug.js';

import {
    invalidateRoute,
} from '../../router/route-invalidation.js';

import {
    invalidatePrefetch,
} from '../../router/prefetch/prefetch.js';

// =========================================
// CONFIG
// =========================================

const FORM_SELECTOR =
    '.form-layout[data-form-page="ajouter"]';

const HOME_ROUTE =
    '/lolissr/';

// =========================================
// HELPERS
// =========================================

function updateUploadText(
    input,
    textElement,
)
{
    textElement.textContent =
        input.files?.length
            ? input.files[0].name
            : 'Choisir une image';
}

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

    const livreInput =
        $('#livre');

    const slugInput =
        $('#slug');

    const imageInput =
        $('#image');

    const uploadText =
        $('.form-upload-text');

    let slugEditedManually =
        false;

    /*
    |--------------------------------------------------------------------------
    | AUTO SLUG
    |--------------------------------------------------------------------------
    */

    if (
        slugInput
        instanceof HTMLInputElement
    ) {

        slugInput.addEventListener(
            'input',
            () =>
            {
                slugEditedManually =
                    true;
            },
        );
    }

    if (
        livreInput
        instanceof HTMLInputElement
        && slugInput
        instanceof HTMLInputElement
    ) {

        livreInput.addEventListener(
            'input',
            () =>
            {
                if (
                    slugEditedManually
                ) {
                    return;
                }

                slugInput.value =
                    generateSlug(
                        livreInput.value,
                    );
            },
        );
    }

    /*
    |--------------------------------------------------------------------------
    | IMAGE LABEL
    |--------------------------------------------------------------------------
    */

    if (
        imageInput
        instanceof HTMLInputElement
        && uploadText
    ) {

        imageInput.addEventListener(
            'change',
            () =>
            {
                updateUploadText(
                    imageInput,
                    uploadText,
                );
            },
        );
    }

    /*
    |--------------------------------------------------------------------------
    | SUBMIT
    |--------------------------------------------------------------------------
    */

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
                    'AJOUTER',
                    'submit-start',
                );

                /*
                |--------------------------------------------------------------------------
                | REQUEST
                |--------------------------------------------------------------------------
                */

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
                    'AJOUTER',
                    'response',
                    data,
                );

                /*
                |--------------------------------------------------------------------------
                | ERROR
                |--------------------------------------------------------------------------
                */

                if (
                    !data?.success
                ) {

                    showToast(
                        data?.message
                        || 'Une erreur est survenue',
                        'error',
                    );

                    return;
                }

                /*
                |--------------------------------------------------------------------------
                | INVALIDATE
                |--------------------------------------------------------------------------
                */

                invalidateRoute(
                    HOME_ROUTE,
                );

                invalidatePrefetch(
                    HOME_ROUTE,
                );

                /*
                |--------------------------------------------------------------------------
                | SUCCESS
                |--------------------------------------------------------------------------
                */

                showToast(
                    data.message
                    || 'Manga ajouté avec succès',
                    'success',
                );

                /*
                |--------------------------------------------------------------------------
                | RESET
                |--------------------------------------------------------------------------
                */

                form.reset();

                slugEditedManually =
                    false;

                if (
                    imageInput
                    instanceof HTMLInputElement
                    && uploadText
                ) {

                    updateUploadText(
                        imageInput,
                        uploadText,
                    );
                }

                debug(
                    'AJOUTER',
                    'success',
                );

            } catch (error) {

                debugError(
                    'AJOUTER',
                    error,
                );

                showToast(
                    error?.data?.message
                    || error.message
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
                    'AJOUTER',
                    'submit-end',
                );
            }
        },
    );

    debug(
        'AJOUTER',
        'initialized',
    );
}