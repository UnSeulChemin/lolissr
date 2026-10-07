// =================================================
// SUPPRESSION NENDOROID
// =================================================

import { post } from '../../core/http.js';

import { delegate } from '../../core/dom.js';

import { showToast } from '../../core/toast.js';

import { debug } from '../../core/debug/debug.js';

import { handleError } from '../../core/errors/error-handler.js';

import { FrontendError } from '../../core/errors/frontend-error.js';

import { navigateTo } from '../../router/navigation/navigation.js';

import { invalidateNendoroidPages } from '../cache-invalidation.js';

import { confirmDelete } from '../../core/modal/delete-modal.js';

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
// SUPPRESSION NENDOROID
// =================================================

async function deleteNendoroid(button)
{
    if (button.disabled)
    {

        return;
    }

    const url = button.dataset.url;

    const redirectUrl = button.dataset.redirect
        || '/';

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

    const confirmed = await confirmDelete(button, 'Supprimer cette Nendoroid ?');

    if (!confirmed)
    {
        return;
    }

    if (!button.dataset.originalText)
    {

        button.dataset.originalText = button.textContent
            || 'Supprimer';
    }

    setLoadingState(button, true);

    try
    {

        debug('DELETE_NENDOROID', 'request', url);

        const data = await post(
                url,
                {},
                {
                    headers: {
                        Accept: 'application/json'
                    }
                }
            );

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

        const target = data.data?.redirect
            || redirectUrl;

        invalidateNendoroidPages();
        if (!button.isConnected) return;

        showToast(data.message || 'Nendoroid supprimée', 'success');

        await navigateTo(target);

    } catch (error)
    {

        if (!button.isConnected) return;
        handleError(error);

        setLoadingState(button, false);
    }
}

// =================================================
// INITIALISATION
// =================================================

export function initDeleteNendoroid()
{
    if (initialized)
    {

        return;
    }

    initialized = true;

    delegate(
        document,
        'click',
        '.js-delete-nendoroid',
        (_, button) =>
        {
            if (!( button instanceof HTMLButtonElement ))
            {

                return;
            }

            void deleteNendoroid(button);
        }
    );

    debug('DELETE_NENDOROID', 'initialized');
}
