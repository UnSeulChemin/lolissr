// ==================================================
// APP ERRORS
// ==================================================

import {
    debug,
} from '../core/debug/debug.js';

import {
    handleError,
} from '../core/errors/error-handler.js';

// ==================================================
// INIT
// ==================================================

export function initGlobalErrorHandlers()
{
    /*
    |--------------------------------------------------------------------------
    | PROMISE ERRORS
    |--------------------------------------------------------------------------
    */

    window.addEventListener(
        'unhandledrejection',
        (
            event,
        ) =>
        {
            handleError(
                event.reason,
            );
        },
    );

    /*
    |--------------------------------------------------------------------------
    | JS ERRORS
    |--------------------------------------------------------------------------
    */

    window.addEventListener(
        'error',
        (
            event,
        ) =>
        {
            handleError(
                event.error,
            );
        },
    );

    debug(
        'ERROR_HANDLER',
        'initialized',
    );
}