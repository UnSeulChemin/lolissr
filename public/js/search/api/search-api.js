// =========================================
// SEARCH API
// =========================================

import {
    get,
} from '../../core/http.js';

import {
    debugError,
} from '../../core/debug/debug.js';

import {
    FrontendError,
} from '../../core/errors/FrontendError.js';

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

        if (
            error?.name === 'AbortError'
            || signal?.aborted
        ) {
            return [];
        }

        debugError(
            'SEARCH_API',
            error,
        );

        if (
            error instanceof FrontendError
        ) {
            error.silent =
                true;
        }

        return [];
    }
}