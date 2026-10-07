// =================================================
// SUPPRESSION VOCABULAIRE
// =================================================

import { post } from '../../core/http.js';

import { delegate } from '../../core/dom.js';

import { showToast } from '../../core/toast.js';

import { debug } from '../../core/debug/debug.js';

import { handleError } from '../../core/errors/error-handler.js';

import { FrontendError } from '../../core/errors/frontend-error.js';

import { confirmDelete } from '../../core/modal/delete-modal.js';

import { invalidateVocabularyPages } from '../cache-invalidation.js';

// =================================================
// ÉTAT
// =================================================

let initialized = false;

// =================================================
// SUPPRESSION
// =================================================

async function deleteVocabulaire(button)
{
    if (button.disabled)
    {
        return;
    }

    const id = Number(button.dataset.id);

    const url = button.dataset.url;

    const item = button.closest('.chinois-vocab-card');

    // --------------------------------------------------------------------------
    // VALIDATION
    // --------------------------------------------------------------------------

    if (!url || id <= 0)
    {

        handleError(
            new FrontendError(
                'Paramètres invalides',
                {
                    code: 'INVALID_VOCAB_DELETE'
                }
            )
        );

        return;
    }

    // --------------------------------------------------------------------------
    // CONFIRMATION
    // --------------------------------------------------------------------------

    const confirmed = await confirmDelete(button, 'Supprimer ce vocabulaire ?');

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
            'VOCABULAIRE_DELETE',
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
                    code: 'DELETE_VOCAB_FAILED'
                }
            );
        }

        // --------------------------------------------------------------------------
        // INVALIDATION
        // --------------------------------------------------------------------------

        invalidateVocabularyPages();
        if (!button.isConnected) return;

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

        showToast(data.message || 'Vocabulaire supprimé', 'success');

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

export function initDeleteVocabulary()
{
    if (initialized)
    {
        return;
    }

    initialized = true;

    delegate(
        document,
        'click',
        '.vocabulaire-delete',
        (_, button) =>
        {
            if (!( button instanceof HTMLButtonElement ))
            {
                return;
            }

            if (!button .closest( '.chinois-vocab-card' ))
            {
                return;
            }

            void deleteVocabulaire(button);
        }
    );

    debug('VOCABULAIRE_DELETE', 'initialized');
}
