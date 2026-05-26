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
// Helpers
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
    // =====================================
    // Form
    // =====================================

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
        form.dataset.ajouterPageInitialized
        === 'true'
    ) {
        return;
    }

    form.dataset.ajouterPageInitialized =
        'true';

    // =====================================
    // Inputs
    // =====================================

    const livreInput =
        $(
            '#livre',
        );

    const slugInput =
        $(
            '#slug',
        );

    const imageInput =
        $(
            '#image',
        );

    const uploadText =
        $(
            '.form-upload-text',
        );

    // =====================================
    // Auto Slug
    // =====================================

    let slugEditedManually =
        false;

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

    // =====================================
    // Upload Preview
    // =====================================

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

    // =====================================
    // Submit
    // =====================================

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
                    'submit',
                );

                const data =
                    await request(
                        form.action,
                        {
                            method:
                                'POST',

                            headers:
                            {
                                'Accept':
                                    'application/json',
                            },

                            body:
                                new FormData(
                                    form,
                                ),
                        },
                    );

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
                    error instanceof Error
                        ? error.message
                        : 'Erreur serveur',
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
        },
    );

    debug(
        'AJOUTER',
        'initialized',
    );
}