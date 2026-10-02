// =================================================
// RECHERCHE API
// =================================================

import {
    get,
} from '../../core/http.js';

import {
    debugError,
} from '../../core/debug/debug.js';

import {
    FrontendError,
} from '../../core/errors/FrontendError.js';

// =================================================
// CHARGEMENT DES RÉSULTATS DE RECHERCHE
// =================================================

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
            response?.data
            ?? {}
        );

    } catch (error) {

        if (
            error?.name === 'AbortError'
            || signal?.aborted
        ) {
            throw new DOMException('Search aborted', 'AbortError');
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

        throw error;
    }
}
