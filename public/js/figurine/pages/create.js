// =================================================
// PAGE D'AJOUT
// =================================================

import { request } from '../../core/http.js';
import { formErrorMessage } from '../../core/form-errors.js';

import { $ } from '../../core/dom.js';

import { showToast } from '../../core/toast.js';

import { debug, debugError } from '../../core/debug/debug.js';

import { generateSlug } from '../../core/slug.js';

// À créer ensuite
import { invalidateFigurinePages } from '../cache-invalidation.js';

// =================================================
// CONFIGURATION
// =================================================

const FORM_SELECTOR = '.form-layout[data-form-page="ajouter"]';

// =================================================
// UTILITAIRES
// =================================================

function updateUploadText(input, textElement)
{
    textElement.textContent = input.files?.length
            ? input.files[0].name
            : 'Choisir une image';
}

// =================================================
// INITIALISATION
// =================================================

export function initCreatePage()
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
    let submitting = false;

    const originInput = $('#origin');
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

    if (originInput instanceof HTMLInputElement && slugInput instanceof HTMLInputElement)
    {
        originInput.addEventListener('input', () =>
        {
            if (slugEditedManually)
            {
                return;
            }

            slugInput.value = generateSlug(originInput.value);
        });
    }

    if (imageInput instanceof HTMLInputElement && uploadText)
    {
        imageInput.addEventListener('change', () =>
        {
            updateUploadText(imageInput, uploadText);
        });
    }

    form.addEventListener('submit', async (event) =>
    {
        event.preventDefault();
        if (submitting) return;
        submitting = true;

        const submitButton = form.querySelector('[type="submit"]');

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
                    body: new FormData(form)
                }
            );

            if (!data?.success)
            {
                if (!form.isConnected) return;
                showToast(data?.message ?? 'Une erreur est survenue', 'error');

                return;
            }

            invalidateFigurinePages();
            if (!form.isConnected) return;

            showToast(data.message ?? 'Figurine ajoutée avec succès', 'success');

            form.reset();

            slugEditedManually = false;

            if (imageInput instanceof HTMLInputElement && uploadText)
            {
                updateUploadText(imageInput, uploadText);
            }
        }
        catch (error)
        {
            if (!form.isConnected) return;
            debugError('FIGURINE_AJOUTER', error);

            showToast(formErrorMessage(error), 'error');
        }
        finally
        {
            submitting = false;
            if (submitButton instanceof HTMLButtonElement)
            {
                submitButton.disabled = false;
            }
        }
    });

    debug('FIGURINE_AJOUTER', 'initialized');
}
