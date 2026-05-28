// =========================================
// HANDLE ROUTER ERROR
// =========================================

import {
    FrontendError,
} from './FrontendError.js';

// =========================================
// HANDLE
// =========================================

export function handleRouterError(
    error,
)
{
    /*
    |--------------------------------------------------------------------------
    | FRONTEND ERROR
    |--------------------------------------------------------------------------
    */

    if (
        error instanceof FrontendError
    ) {

        return error;
    }

    /*
    |--------------------------------------------------------------------------
    | ABORT
    |--------------------------------------------------------------------------
    */

    if (
        error?.name
        === 'AbortError'
    ) {

        return new FrontendError(
            'Navigation annulée',
            {
                code:
                    'NAVIGATION_ABORTED',

                silent:
                    true,
            },
        );
    }

    /*
    |--------------------------------------------------------------------------
    | UNKNOWN
    |--------------------------------------------------------------------------
    */

    return new FrontendError(
        error?.message
        || 'Erreur navigation',
        {
            code:
                'ROUTER_ERROR',
            },
    );
}