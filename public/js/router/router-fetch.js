// =========================================
// ROUTER FETCH
// =========================================

import {
    debug,
    debugError,
} from '../core/debug/debug.js';

import {
    FrontendError,
} from '../core/errors/FrontendError.js';

import {
    request,
} from '../core/http.js';

import {
    normalizeCacheKey,
} from '../core/navigation.js';

// =========================================
// FETCH PAGE
// =========================================

export async function fetchPage(
    href,
    options = {},
)
{
    const url =
        normalizeCacheKey(
            href,
        );

    try
    {
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
        )
        {
            throw new FrontendError(
                'Réponse page invalide',
                {
                    code:
                        'INVALID_PAGE_RESPONSE',
                },
            );
        }

        if (
            typeof response.page?.html
            !== 'string'
        )
        {
            throw new FrontendError(
                'HTML page invalide',
                {
                    code:
                        'INVALID_PAGE_HTML',
                },
            );
        }

        debug(
            'FETCH',
            'success',
            url,
        );

        return response;
    }
    catch (error)
    {
        debugError(
            'FETCH',
            error,
        );

        throw error;
    }
}