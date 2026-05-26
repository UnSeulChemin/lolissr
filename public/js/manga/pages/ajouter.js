// ==================================================
// Ajouter Page
// ==================================================

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

// ==================================================
// Config
// ==================================================

const FORM_SELECTOR =
    '.form-layout[data-form-page="ajouter"]';

// ==================================================
// Helpers
// ==================================================

function getCsrfToken()
{
    return (
        window.csrfToken
        || document
            .querySelector(
                'meta[name="csrf-token"]',
            )
            ?.getAttribute(
                'content',
            )
        || ''
    );
}

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

// ==================================================
// Init
// ==================================================

export function initAjouterPage()
{
    // ==============================================
    // Form
    // ==============================================

    const form =
        document.querySelector(
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

    // ==============================================
    // Prevent double init
    // ==============================================

    if (
        form.dataset.ajouterPageInitialized
        === 'true'
    ) {
        return;
    }

    form.dataset.ajouterPageInitialized =
        'true';

    // ==============================================
    // Inputs
    // ==============================================

    const livreInput =
        document.getElementById(
            'livre',
        );

    const slugInput =
        document.getElementById(
            'slug',
        );

    const imageInput =
        document.getElementById(
            'image',
        );

    const uploadText =
        document.querySelector(
            '.form-upload-text',
        );

    // ==============================================
    // Auto slug
    // ==============================================

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

    // ==============================================
    // Upload preview
    // ==============================================

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

    // ==============================================
    // Submit
    // ==============================================

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

            const formData =
                new FormData(
                    form,
                );

            if (
                !formData.has(
                    'csrf_token',
                )
            ) {

                formData.append(
                    'csrf_token',
                    getCsrfToken(),
                );
            }

            try {

                debug(
                    'AJOUTER',
                    'submit',
                );

                const response =
                    await fetch(
                        form.action,
                        {
                            method:
                                'POST',

                            credentials:
                                'same-origin',

                            headers:
                            {
                                'X-Requested-With':
                                    'XMLHttpRequest',

                                'Accept':
                                    'application/json',
                            },

                            body:
                                formData,
                        },
                    );

                const contentType =
                    response.headers.get(
                        'content-type',
                    )
                    || '';

                if (
                    !contentType.includes(
                        'application/json',
                    )
                ) {

                    throw new Error(
                        'Réponse serveur invalide',
                    );
                }

                const data =
                    await response.json();

                if (
                    !response.ok
                    || !data.success
                ) {

                    showToast(
                        data.message
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