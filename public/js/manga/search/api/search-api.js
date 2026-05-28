// =========================================
// SEARCH API
// =========================================

import {
    get,
} from '../../../core/http.js';

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

        console.error(
            '[SEARCH API]',
            error,
        );

        return [];
    }
}