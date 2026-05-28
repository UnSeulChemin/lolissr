// =========================================
// ROUTER FETCH
// =========================================

import {
    request,
} from '../core/http.js';

import {
    normalizeUrl,
} from '../core/navigation.js';

import {
    debug,
    debugError,
} from '../core/debug.js';

// =========================================
// FETCH PAGE
// =========================================

export async function fetchPage(
    href,
    options = {},
)
{
    const url =
        normalizeUrl(
            href,
        );

    try {

        debug(
            'FETCH',
            'network',
            url,
        );

        const response =
            await request(
                url,
                {
                    signal:
                        options.signal,

                    headers:
                    {
                        Accept:
                            'application/json',
                    },
                },
            );

        /*
        |--------------------------------------------------------------------------
        | VALIDATION
        |--------------------------------------------------------------------------
        */

        if (
            response?.type
            !== 'page'
        ) {

            throw new Error(
                'Invalid page response',
            );
        }

        if (
            typeof response.page?.html
            !== 'string'
        ) {

            throw new Error(
                'Invalid page HTML',
            );
        }

        debug(
            'FETCH',
            'success',
            url,
        );

        return response;

    } catch (error) {

        debugError(
            'FETCH',
            error,
        );

        throw error;
    }
}