// =================================================
// AJOUTER CHINOIS
// =================================================

import { request } from '../../core/http.js';

import { $ } from '../../core/dom.js';

import { showToast } from '../../core/toast.js';

import { debug, debugError } from '../../core/debug/debug.js';

import { invalidateGrammarPages, invalidateVocabularyPages } from '../cache-invalidation.js';

// =================================================
// CONFIGURATION
// =================================================

const FORM_SELECTOR = '.form-layout[data-form-page]';

// =================================================
// INVALIDATION
// =================================================

function invalidatePages(formPage)
{
    switch (formPage)
    {
        case 'ajouter-vocabulaire': invalidateVocabularyPages();

            break;

        case 'ajouter-grammaire': invalidateGrammarPages();

            break;
    }
}

// =================================================
// INITIALISATION
// =================================================

export function initCreatePage()
{
    const form = $(FORM_SELECTOR);

    if (!( form instanceof HTMLFormElement ))
    {
        return;
    }

    if (form.dataset.initialized === 'true')
    {
        return;
    }

    form.dataset.initialized = 'true';
    let submitting = false;

    form.addEventListener(
        'submit',
        async (event) =>
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

                debug('CHINOIS', 'submit-start');

                // =================================================
                // REQUÊTE
                // =================================================

                const data = await request(
                        form.action,
                        {
                            method: 'POST',

                            body: new FormData(form)
                        }
                    );

                debug('CHINOIS', 'response', data);

                // =================================================
                // ERREUR
                // =================================================

                if (!data?.success)
                {
                if (!form.isConnected) return;

                    showToast(data?.message || 'Une erreur est survenue', 'error');

                    return;
                }

                // =================================================
                // INVALIDATION
                // =================================================

                invalidatePages(form.dataset.formPage ?? '');
                if (!form.isConnected) return;

                // =================================================
                // SUCCÈS
                // =================================================

                showToast(data.message || 'Ajout effectué', 'success');

                // =================================================
                // RÉINITIALISATION
                // =================================================

                form.reset();

                debug('CHINOIS', 'success');

            } catch (error)
            {
                if (!form.isConnected) return;

                debugError('CHINOIS', error);

                const errors = error?.details
                        ?.data
                        ?.errors;

                if (errors && Object.keys(errors).length)
                {

                    showToast(Object.values( errors )[0], 'error');

                    return;
                }

                showToast(error?.message || 'Erreur serveur', 'error');

            } finally
            {
                submitting = false;

                if (submitButton instanceof HTMLButtonElement)
                {

                    submitButton.disabled = false;
                }

                debug('CHINOIS', 'submit-end');
            }
        }
    );

    debug('CHINOIS', 'initialized');
}
