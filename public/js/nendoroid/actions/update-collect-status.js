// =================================================
// MISE À JOUR DU STATUT DE COLLECTION
// =================================================

import { post } from '../../core/http.js';

import { $$, delegate } from '../../core/dom.js';

import { showToast } from '../../core/toast.js';

import { debug } from '../../core/debug/debug.js';

import { handleError } from '../../core/errors/error-handler.js';

import { FrontendError } from '../../core/errors/FrontendError.js';

import { invalidateNendoroidPages } from '../nendoroid-cache.js';

import { updateHeaderUser } from '../../profile/header-user.js';

// =================================================
// CONFIGURATION
// =================================================

const BUTTON_SELECTOR = '.js-nendoroid-collect-status-button';

// =================================================
// ÉTAT
// =================================================

let initialized = false;

// =================================================
// UTILITAIRES
// =================================================

function updateCollectButtonState(button, collectStatus)
{
    const isCollected = Number(collectStatus) === 1;

    button.dataset.collectStatus = String(collectStatus);

    button.classList.toggle('active', isCollected);

    const label = isCollected
            ? 'Retirer de la collection'
            : 'Ajouter à la collection';

    button.title = label;

    button.setAttribute('aria-label', label);

    button.setAttribute('aria-pressed', isCollected ? 'true' : 'false');
}

function refreshButtons()
{
    $$(BUTTON_SELECTOR).forEach(
        (button) =>
        {
            if (!( button instanceof HTMLButtonElement ))
            {
                return;
            }

            updateCollectButtonState(button, Number( button.dataset.collectStatus ?? 0 ));
        }
    );
}

// =================================================
// MISE À JOUR
// =================================================

async function updateCollectStatus(button)
{
    if (button.disabled)
    {
        return;
    }

    const url = button.dataset.url;

    if (!url)
    {
        return;
    }

    const currentCollectStatus = Number(button.dataset.collectStatus ?? 0);

    const nextCollectStatus = currentCollectStatus === 1
            ? 0
            : 1;

    // --------------------------------------------------------------------------
    // OPTIMISTE INTERFACE
    // --------------------------------------------------------------------------

    button.disabled = true;

    updateCollectButtonState(button, nextCollectStatus);

    try
    {
        const data = await post(
                url,
                {
                    collectStatus: nextCollectStatus
                },
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
                || 'Erreur mise à jour',
                {
                    code: 'NENDOROID_COLLECT_STATUS_UPDATE_FAILED'
                }
            );
        }

        // --------------------------------------------------------------------------
        // APPLICATION SERVEUR ÉTAT
        // --------------------------------------------------------------------------

        const collectStatus = Number(data?.data?.collectStatus ?? nextCollectStatus);

        updateCollectButtonState(button, collectStatus);

        updateHeaderUser(data?.data?.level);

        invalidateNendoroidPages();

        // --------------------------------------------------------------------------
        // SUCCÈS
        // --------------------------------------------------------------------------

        let message = data?.message
            || 'Mise à jour effectuée';

        if (data?.data?.xpEarned)
        {
            message += ' ⭐ +20 XP';
        }

        showToast(message, 'success');
    }
    catch (error)
    {
        updateCollectButtonState(button, currentCollectStatus);

        handleError(error);
    }
    finally
    {
        button.disabled = false;
    }
}

// =================================================
// INITIALISATION
// =================================================

export function initUpdateNendoroidCollectStatus()
{
    if (initialized)
    {
        return;
    }

    initialized = true;

    delegate(
        document,
        'click',
        BUTTON_SELECTOR,
        (_, button) =>
        {
            if (!( button instanceof HTMLButtonElement ))
            {
                return;
            }

            void updateCollectStatus(button);
        }
    );

    document.addEventListener(
        'router:loaded',
        refreshButtons,
        {
            passive: true
        }
    );

    refreshButtons();

    debug('NENDOROID_COLLECT_STATUS', 'initialized');
}