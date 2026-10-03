// =================================================
// ERREURS DE L’APPLICATION
// =================================================

import { debug } from '../core/debug/debug.js';

import { handleError } from '../core/errors/error-handler.js';

// =================================================
// INITIALISATION
// =================================================

export function initGlobalErrorHandlers()
{
    // --------------------------------------------------------------------------
    // PROMESSES ERREURS
    // --------------------------------------------------------------------------

    window.addEventListener(
        'unhandledrejection',
        (event) =>
        {
            handleError(event.reason);
        }
    );

    // --------------------------------------------------------------------------
    // JS ERREURS
    // --------------------------------------------------------------------------

    window.addEventListener(
        'error',
        (event) =>
        {
            handleError(event.error);
        }
    );

    debug('ERROR_HANDLER', 'initialized');
}