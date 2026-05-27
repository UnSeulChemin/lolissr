// =========================================
// SEARCH API
// =========================================

import {
    get,
} from '../../core/http.js';

// =========================================
// FETCH SEARCH RESULTS
// =========================================

export async function fetchSearchResults(
    url,
    signal,
)
{
    return get(
        url,
        {
            signal,

            responseType:
                'json',

            headers:
            {
                Accept:
                    'application/json',

                'X-Partial':
                    'true',
            },
        },
    );
}