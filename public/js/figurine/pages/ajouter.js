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
} from '../../manga/utils/slug.js';

// À créer ensuite
import {
    invalidateFigurinePages,
} from '../figurine-cache.js';

// =========================================
// CONFIG
// =========================================

const FORM_SELECTOR =
    '.form-layout[data-form-page="ajouter"]';

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
    const form = $(FORM_SELECTOR);

    if (!(form instanceof HTMLFormElement))
    {
        return;
    }

    if (form.dataset.initialized === 'true')
    {
        return;
    }

    form.dataset.initialized = 'true';

    const waifuInput = $('#waifu');
    const slugInput = $('#slug');
    const imageInput = $('#image');
    const uploadText = $('.form-upload-text');

    let slugEditedManually = false;

    if (slugInput instanceof HTMLInputElement)
    {
        slugInput.addEventListener('input', () =>
        {
            slugEditedManually = true;
        });
    }

    if (
        waifuInput instanceof HTMLInputElement
        && slugInput instanceof HTMLInputElement
    )
    {
        waifuInput.addEventListener('input', () =>
        {
            if (slugEditedManually)
            {
                return;
            }

            slugInput.value = generateSlug(waifuInput.value);
        });
    }

    if (
        imageInput instanceof HTMLInputElement
        && uploadText
    )
    {
        imageInput.addEventListener('change', () =>
        {
            updateUploadText(
                imageInput,
                uploadText,
            );
        });
    }

    form.addEventListener('submit', async (event) =>
    {
        event.preventDefault();

        const submitButton =
            form.querySelector('[type="submit"]');

        if (submitButton instanceof HTMLButtonElement)
        {
            submitButton.disabled = true;
        }

        try
        {
            const data = await request(
                form.action,
                {
                    method: 'POST',
                    body: new FormData(form),
                },
            );

            if (!data?.success)
            {
                showToast(
                    data?.message ?? 'Une erreur est survenue',
                    'error',
                );

                return;
            }

            invalidateFigurinePages();

            showToast(
                data.message ?? 'Figurine ajoutée avec succès',
                'success',
            );

            form.reset();

            slugEditedManually = false;

            if (
                imageInput instanceof HTMLInputElement
                && uploadText
            )
            {
                updateUploadText(
                    imageInput,
                    uploadText,
                );
            }
        }
        catch (error)
        {
            debugError(
                'FIGURINE_AJOUTER',
                error,
            );

            showToast(
                error?.data?.message
                ?? error.message
                ?? 'Erreur serveur',
                'error',
            );
        }
        finally
        {
            if (submitButton instanceof HTMLButtonElement)
            {
                submitButton.disabled = false;
            }
        }
    });

    debug(
        'FIGURINE_AJOUTER',
        'initialized',
    );
}