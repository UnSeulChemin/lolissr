// =================================================
// SUPPRESSION GRAMMAIRE
// =================================================

import { post } from '../../core/http.js';

import { delegate } from '../../core/dom.js';

import { showToast } from '../../core/toast.js';

import { debug } from '../../core/debug/debug.js';

import { handleError } from '../../core/errors/error-handler.js';

import { FrontendError } from '../../core/errors/frontend-error.js';

import { deleteModal } from '../../core/modal/modal.js';

import { invalidateGrammarPages } from '../cache-invalidation.js';

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

    const confirmed = await deleteModal('Supprimer cette règle de grammaire ?');

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