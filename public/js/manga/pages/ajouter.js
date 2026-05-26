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
} from '../../core/debug.js';

import {
    generateSlug,
} from '../utils/slug.js';

// =========================================
// Config
// =========================================

const FORM_SELECTOR =
    '.form-layout[data-form-page="ajouter"]';

// =========================================
// Upload Text
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
// Init
// =========================================

export function initAjouterPage()
{
    const form =
        $(
            FORM_SELECTOR,
        );

    if (
        !(
            form
            instanceof HTMLFormElement
        )
    ) {
        return;
    }

    // =====================================
    // Prevent Double Init
    // =====================================

    if (
        form.dataset.initialized
        === 'true'
    ) {
        return;
    }

    form.dataset.initialized =
        'true';

    // =====================================
    // Inputs
    // =====================================

    const livreInput =
        $('#livre');

    const slugInput =
        $('#slug');

    const imageInput =
        $('#image');

    const uploadText =
        $('.form-upload-text');

    // =====================================
    // Auto Slug
    // =====================================

    let slugEditedManually =
        false;

    if (
        slugInput
        instanceof HTMLInputElement
    ) {

        slugInput.oninput =
            () =>
            {
                slugEditedManually =
                    true;
            };
    }

    if (
        livreInput
        instanceof HTMLInputElement
        && slugInput
        instanceof HTMLInputElement
    ) {

        livreInput.oninput =
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
            };
    }

    // =====================================
    // Upload Preview
    // =====================================

    if (
        imageInput
        instanceof HTMLInputElement
        && uploadText
    ) {

        imageInput.onchange =
            () =>
            {
                updateUploadText(
                    imageInput,
                    uploadText,
                );
            };
    }

    // =====================================
    // Submit
    // =====================================

    form.onsubmit =
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
                    'submit',
                );

                const data =
                    await request(
                        form.action,
                        {
                            method:
                                'POST',

                            responseType:
                                'json',

                            headers:
                            {
                                Accept:
                                    'application/json',
                            },

                            body:
                                new FormData(
                                    form,
                                ),
                        },
                    );

                debug(
                    'AJOUTER',
                    data,
                );

                // =============================
                // Error
                // =============================

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

                // =============================
                // Success
                // =============================

                showToast(
                    data.message
                    || 'Manga ajouté avec succès',
                    'success',
                );

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
            }
        };

    debug(
        'AJOUTER',
        'initialized',
    );
}