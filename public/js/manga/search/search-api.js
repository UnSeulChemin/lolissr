// =========================================
// SEARCH API
// =========================================

import {
    get,
} from '../../core/http.js';

export async function fetchSearchResults(
    url,
    signal,
)
{
    return get(
        url,
        {
            signal,

            headers:
            {
                'X-Partial':
                    'true',

                'Accept':
                    'application/json',
            },
        },
    );
}