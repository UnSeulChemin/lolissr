// =================================================
// SUPPRESSION MANGA
// =================================================

import { post } from '../../core/http.js';

import { delegate } from '../../core/dom.js';

import { showToast } from '../../core/toast.js';

import { debug } from '../../core/debug/debug.js';

import { handleError } from '../../core/errors/error-handler.js';

import { FrontendError } from '../../core/errors/frontend-error.js';

import { navigateTo } from '../../router/navigation/navigation.js';

import { invalidateMangaPages } from '../cache.js';

import { deleteModal } from '../../core/modal/modal.js';

// =================================================
// ÉTAT
// =================================================

let initialized = false;

// =================================================
// INTERFACE
// =================================================

function setLoadingState(button, loading)
{
    button.disabled = loading;

    button.textContent = loading
            ? 'Suppression...'
            : (button.dataset.originalText || 'Supprimer');
}

// =================================================
// SUPPRESSION MANGA
// =================================================

async function deleteManga(button)
{
    if (button.disabled)
    {

        return;
    }

    const url = button.dataset.url;

    const redirectUrl = button.dataset.redirect
        || '/';

    // --------------------------------------------------------------------------
    // URL
    // --------------------------------------------------------------------------

    if (!url)
    {

        handleError(
            new FrontendError(
                'URL invalide',
                {
                    code: 'INVALID_DELETE_URL'
                }
            )
        );

        return;
    }

    // --------------------------------------------------------------------------
    // CONFIRMATION
    // --------------------------------------------------------------------------

    const confirmed = await deleteModal('Supprimer ce manga ?');

    if (!confirmed)
    {
        return;
    }

    // --------------------------------------------------------------------------
    // CONSERVATION DU TEXTE ORIGINAL
    // --------------------------------------------------------------------------

    if (!button.dataset.originalText)
    {

        button.dataset.originalText = button.textContent
            || 'Supprimer';
    }

    // --------------------------------------------------------------------------
    // CHARGEMENT
    // --------------------------------------------------------------------------

    setLoadingState(button, true);

    try
    {

        debug('DELETE', 'request', url);

        const data = await post(
                url,
                {},
                {
                    headers: {
                        Accept: 'application/json'
                    }
                }
            );

        // --------------------------------------------------------------------------
        // VALIDATION
        // --------------------------------------------------------------------------

        if (data?.success !== true)
        {

            throw new FrontendError(
                data?.message
                || 'Erreur suppression',
                {
                    code: 'DELETE_FAILED'
                }
            );
        }

        // --------------------------------------------------------------------------
        // REDIRECTION
        // --------------------------------------------------------------------------

        const target = data.data?.redirect
            || redirectUrl;

        // --------------------------------------------------------------------------
        // INVALIDATION
        // --------------------------------------------------------------------------

        invalidateMangaPages();

        // --------------------------------------------------------------------------
        // SUCCÈS
        // --------------------------------------------------------------------------

        showToast(data.message || 'Supprimé', 'success');

        // --------------------------------------------------------------------------
        // NAVIGATION
        // --------------------------------------------------------------------------

        await navigateTo(target);

    } catch (error)
    {

        handleError(error);

        setLoadingState(button, false);
    }
}

// =================================================
// INITIALISATION
// =================================================

export function initDeleteManga()
{
    if (initialized)
    {

        return;
    }

    initialized = true;

    delegate(
        document,
        'click',
        '.js-delete-manga',
        (_, button) =>
        {
            if (!( button instanceof HTMLButtonElement ))
            {

                return;
            }

            void deleteManga(button);
        }
    );

    debug('DELETE', 'initialized');
}