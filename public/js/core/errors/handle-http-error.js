// =========================================
// HANDLE HTTP ERROR
// =========================================

import {
    FrontendError,
} from './FrontendError.js';

// =========================================
// HANDLE
// =========================================

export function handleHttpError(
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
    | NETWORK ERROR
    |--------------------------------------------------------------------------
    */

    if (
        error instanceof TypeError
    ) {

        return new FrontendError(
            'Erreur réseau',
            {
                code:
                    'NETWORK_ERROR',

                status:
                    0,
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
        || 'Erreur HTTP inconnue',
        {
            code:
                'HTTP_UNKNOWN_ERROR',
        },
    );
}