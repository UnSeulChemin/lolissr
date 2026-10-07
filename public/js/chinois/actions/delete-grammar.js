// =================================================
// SUPPRESSION GRAMMAIRE
// =================================================

import { post } from '../../core/http.js';

import { delegate } from '../../core/dom.js';

import { showToast } from '../../core/toast.js';

import { debug } from '../../core/debug/debug.js';

import { handleError } from '../../core/errors/error-handler.js';

import { FrontendError } from '../../core/errors/frontend-error.js';

import { confirmDelete } from '../../core/modal/delete-modal.js';

import { invalidateGrammarPages } from '../cache-invalidation.js';

import { navigateTo } from '../../router/navigation/navigation.js';
import { navigationState } from '../../router/state.js';

// =================================================
// ÉTAT
// =================================================

let initialized = false;

// =================================================
// SUPPRESSION
// =================================================

async function deleteGrammaire(button)
{
    if (button.disabled)
    {

        return;
    }

    const id = Number(button.dataset.id);

    const url = button.dataset.url;

    const item = button.closest('.grammar-item');

    // --------------------------------------------------------------------------
    // VALIDATION
    // --------------------------------------------------------------------------

    if (!url || id <= 0)
    {

        handleError(
            new FrontendError(
                'Paramètres invalides',
                {
                    code: 'INVALID_GRAMMAR_DELETE'
                }
            )
        );

        return;
    }

    // --------------------------------------------------------------------------
    // CONFIRMATION
    // --------------------------------------------------------------------------

    const confirmed = await confirmDelete(button, 'Supprimer cette règle de grammaire ?');

    if (!confirmed)
    {
        return;
    }

    // --------------------------------------------------------------------------
    // CHARGEMENT
    // --------------------------------------------------------------------------

    button.disabled = true;

    try
    {

        debug(
            'GRAMMAIRE_DELETE',
            'request',
            {
                id
            }
        );

        const data = await post(
                url,
                {
                    id
                }
            );

        if (data?.success !== true)
        {

            throw new FrontendError(
                data?.message
                || 'Erreur suppression',
                {
                    code: 'DELETE_GRAMMAR_FAILED'
                }
            );
        }

        // --------------------------------------------------------------------------
        // INVALIDATION
        // --------------------------------------------------------------------------

        invalidateGrammarPages();
        if (!button.isConnected) return;

        const grammarPage = button.closest('[data-grammar-url]');
        if (grammarPage)
        {
            const target = new URL(grammarPage.dataset.grammarUrl, location.href);
            target.searchParams.set('reconcile', '1');
            const navigation = navigateTo(target.href, {force: true, updateHistory: false});
            const navigationId = navigationState.navigationId;
            await navigation;
            if (navigationId !== navigationState.navigationId) return;
            const canonical = document.querySelector('[data-grammar-url]')?.dataset.grammarUrl;
            if (canonical) history.replaceState(history.state, '', canonical);
            showToast(data.message || 'Grammaire supprimée', 'success');
            return;
        }

        // --------------------------------------------------------------------------
        // SUPPRESSION CARTE
        // --------------------------------------------------------------------------

        const isFlashcard = document.getElementById('flashcard-counter') !== null;

        if (!isFlashcard)
        {
            item?.remove();
        }
        else
        {
            location.reload();

            return;
        }

        // --------------------------------------------------------------------------
        // SUCCÈS
        // --------------------------------------------------------------------------

        showToast(data.message || 'Grammaire supprimée', 'success');

    } catch (error)
    {

        button.disabled = false;

        if (!button.isConnected) return;
        handleError(error);
    }
}

// =================================================
// INITIALISATION
// =================================================

export function initDeleteGrammar()
{
    if (initialized)
    {

        return;
    }

    initialized = true;

    delegate(
        document,
        'click',
        '.grammaire-delete',
        (_, button) =>
        {
            if (!( button instanceof HTMLButtonElement ))
            {

                return;
            }

            void deleteGrammaire(button);
        }
    );

    debug('GRAMMAIRE_DELETE', 'initialized');
}
