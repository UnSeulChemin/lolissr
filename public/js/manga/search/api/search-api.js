// =========================================
// SEARCH API
// =========================================

import {
    get,
} from '../../../core/http.js';

import {
    debugError,
} from '../../../core/debug.js';

import {
    FrontendError,
} from '../../../core/errors/FrontendError.js';

// =========================================
// FETCH SEARCH RESULTS
// =========================================

export async function fetchSearchResults(
    url,
    signal,
)
{
    try {

        const response =
            await get(
                url,
                {
                    signal,

                    headers:
                    {
                        Accept:
                            'application/json',
                    },
                },
            );

        return (
            response?.data?.results
            ?? []
        );

    } catch (error) {

        /*
        |--------------------------------------------------------------------------
        | ABORT
        |--------------------------------------------------------------------------
        */

        if (
            error?.name
            === 'AbortError'
        ) {

            return [];
        }

        /*
        |--------------------------------------------------------------------------
        | DEBUG
        |--------------------------------------------------------------------------
        */

        debugError(
            'SEARCH_API',
            error,
        );

        /*
        |--------------------------------------------------------------------------
        | SEARCH MUST STAY SILENT
        |--------------------------------------------------------------------------
        */

        if (
            error
            instanceof FrontendError
        ) {

            error.silent =
                true;
        }

        return [];
    }
}