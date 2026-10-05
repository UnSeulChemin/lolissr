// =================================================
// PAGE D'AJOUT
// =================================================

import { request } from '../../core/http.js';

import { $ } from '../../core/dom.js';

import { showToast } from '../../core/toast.js';

import { debug, debugError } from '../../core/debug/debug.js';

import { generateSlug } from '../../core/slug.js';

import { invalidateMangaPages } from '../manga-cache.js';
import { initSeriesSuggestions } from './series-suggestions.js';

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

function updateSourceField(typeInput, sourceInput, sourceLabel)
{
    const isSerie = typeInput.value
        === 'serie';

    sourceLabel.textContent = isSerie
            ? 'Série'
            : 'Auteur';

    sourceInput.placeholder = isSerie
            ? 'Ex : To Love-Ru'
            : 'Ex : Carnelian';
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

    const slugSourceInput = form.querySelector('[data-slug-source]');

    const slugInput = form.querySelector('[data-slug-target]');

    const typeSourceInput = $('#type_source');

    const sourceInput = $('#source');

    const sourceLabel = form.querySelector('label[for="source"]');

    const imageInput = $('#image');

    const uploadText = $('.form-upload-text');

    let slugEditedManually = false;

    const seriesOptions = [...form.querySelectorAll('#manga-existing-series option')];
    const seriesFields = ['editeur', 'statut', 'numero'].map((name) =>
    {
        const input = form.elements.namedItem(name);
        let automaticValue = input?.value;
        // Submitted values after validation must also be preserved.
        let manuallyEdited = Boolean(input?.value && (name !== 'statut' || input.value !== 'en_cours'));
        input?.addEventListener('input', () => { manuallyEdited = true; });
        input?.addEventListener('change', () => { manuallyEdited = true; });
        return {
            fill(series)
            {
                if (!input || manuallyEdited || input.value !== automaticValue) return;
                input.value = series ? series.dataset[name] : (name === 'statut' ? 'en_cours' : '');
                automaticValue = input.value;
            },
            reset()
            {
                automaticValue = input?.value;
                manuallyEdited = Boolean(input?.value && (name !== 'statut' || input.value !== 'en_cours'));
            }
        };
    });

    // --------------------------------------------------------------------------
    // SLUG AUTOMATIQUE
    // --------------------------------------------------------------------------

    if (slugInput instanceof HTMLInputElement)
    {

        slugInput.addEventListener(
            'input',
            () =>
            {
                slugEditedManually = true;
            }
        );
    }

    if (slugSourceInput instanceof HTMLInputElement && slugInput instanceof HTMLInputElement)
    {

        const fillFromSeries = () =>
            {
                const title = slugSourceInput.value.trim().toLocaleLowerCase('fr');
                const matches = seriesOptions.filter((option) =>
                    option.value.trim().toLocaleLowerCase('fr') === title);
                const series = matches.length === 1 ? matches[0] : null;
                seriesFields.forEach((field) => field.fill(series));
                if (slugEditedManually)
                {
                    return;
                }

                slugInput.value = series?.dataset.slug ?? generateSlug(slugSourceInput.value);
            };
        slugSourceInput.addEventListener('input', fillFromSeries);
        slugSourceInput.addEventListener('change', fillFromSeries);
        if (slugSourceInput.value !== '') fillFromSeries();
        initSeriesSuggestions(slugSourceInput, form.querySelector('#manga-series-suggestions'), seriesOptions);
    }

    // --------------------------------------------------------------------------
    // CHAMP SOURCE
    // --------------------------------------------------------------------------

    if (
        typeSourceInput
        instanceof HTMLSelectElement
        && sourceInput
        instanceof HTMLInputElement
        && sourceLabel
        instanceof HTMLLabelElement
    )
    {

        typeSourceInput.addEventListener(
            'change',
            () =>
            {
                updateSourceField(typeSourceInput, sourceInput, sourceLabel);
            }
        );

        updateSourceField(typeSourceInput, sourceInput, sourceLabel);
    }

    // --------------------------------------------------------------------------
    // LIBELLÉ DE L’IMAGE
    // --------------------------------------------------------------------------

    if (imageInput instanceof HTMLInputElement && uploadText)
    {

        imageInput.addEventListener(
            'change',
            () =>
            {
                updateUploadText(imageInput, uploadText);
            }
        );
    }

    // --------------------------------------------------------------------------
    // ENVOI
    // --------------------------------------------------------------------------

    form.addEventListener(
        'submit',
        async (event) =>
        {
            event.preventDefault();

            const submitButton = form.querySelector('[type="submit"]');

            if (submitButton instanceof HTMLButtonElement)
            {

                submitButton.disabled = true;
            }

            try
            {

                debug('AJOUTER', 'submit-start');

                // --------------------------------------------------------------------------
                // REQUÊTE
                // --------------------------------------------------------------------------

                const data = await request(
                        form.action,
                        {
                            method: 'POST',

                            body: new FormData(form)
                        }
                    );

                debug('AJOUTER', 'response', data);

                // --------------------------------------------------------------------------
                // ERREUR
                // --------------------------------------------------------------------------

                if (!data?.success)
                {

                    showToast(data?.message || 'Une erreur est survenue', 'error');

                    return;
                }

                // --------------------------------------------------------------------------
                // INVALIDATION
                // --------------------------------------------------------------------------

                invalidateMangaPages();

                // --------------------------------------------------------------------------
                // SUCCÈS
                // --------------------------------------------------------------------------

                showToast(data.message || 'Manga ajouté avec succès', 'success');

                // --------------------------------------------------------------------------
                // RÉINITIALISATION
                // --------------------------------------------------------------------------

                form.reset();

                slugEditedManually = false;
                seriesFields.forEach((field) => field.reset());

                if (
                    typeSourceInput
                    instanceof HTMLSelectElement
                    && sourceInput
                    instanceof HTMLInputElement
                    && sourceLabel
                    instanceof HTMLLabelElement
                )
                {

                    updateSourceField(typeSourceInput, sourceInput, sourceLabel);
                }

                if (imageInput instanceof HTMLInputElement && uploadText)
                {

                    updateUploadText(imageInput, uploadText);
                }

                debug('AJOUTER', 'success');

            } catch (error)
            {

                debugError('AJOUTER', error);

                showToast(error?.data?.message || error.message || 'Erreur serveur', 'error');

            } finally
            {

                if (submitButton instanceof HTMLButtonElement)
                {

                    submitButton.disabled = false;
                }

                debug('AJOUTER', 'submit-end');
            }
        }
    );

    debug('AJOUTER', 'initialized');
}
