// =========================================
// PREFETCH REQUEST
// =========================================

import {
    request,
} from '../../core/http.js';

import {
    normalizeUrl,
} from '../../core/navigation.js';

import {
    debug,
    debugError,
} from '../../core/debug/debug.js';

import {
    inFlight,
    invalidated,
} from './prefetch-state.js';

import {
    getPrefetchedPage,
    getInFlightPrefetch,
    setPrefetchedPage,
} from './prefetch-cache.js';

// =========================================
// PREFETCH
// =========================================

export async function prefetchPage(
    href,
)
{
    const url =
        normalizeUrl(
            href,
        );

    /*
    |--------------------------------------------------------------------------
    | CURRENT PAGE
    |--------------------------------------------------------------------------
    */

    if (
        url
        === normalizeUrl(
            location.href,
        )
    ) {

        return null;
    }

    /*
    |--------------------------------------------------------------------------
    | INVALIDATED
    |--------------------------------------------------------------------------
    */

    if (
        invalidated.has(
            url,
        )
    ) {

        return null;
    }

    /*
    |--------------------------------------------------------------------------
    | CACHE
    |--------------------------------------------------------------------------
    */

    const cached =
        getPrefetchedPage(
            url,
        );

    if (cached) {

        debug(
            'PREFETCH',
            'cache-hit',
            url,
        );

        return cached;
    }

    /*
    |--------------------------------------------------------------------------
    | IN FLIGHT
    |--------------------------------------------------------------------------
    */

    const existing =
        getInFlightPrefetch(
            url,
        );

    if (existing) {

        debug(
            'PREFETCH',
            'reuse',
            url,
        );

        return existing;
    }

    /*
    |--------------------------------------------------------------------------
    | FETCH
    |--------------------------------------------------------------------------
    */

    debug(
        'PREFETCH',
        'fetch',
        url,
    );

    const promise =
        (async () =>
        {
            try {

                const response =
                    await request(
                        url,
                        {
                            headers:
                            {
                                Accept:
                                    'application/json',

                                'X-Prefetch':
                                    'true',

                                'Cache-Control':
                                    'no-cache',
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

                    debug(
                        'PREFETCH',
                        'invalid-response',
                        url,
                    );

                    return null;
                }

                /*
                |--------------------------------------------------------------------------
                | CACHE
                |--------------------------------------------------------------------------
                */

                setPrefetchedPage(
                    url,
                    response,
                );

                debug(
                    'PREFETCH',
                    'success',
                    url,
                );

                return response;

            } catch (error) {

                debugError(
                    'PREFETCH',
                    error,
                );

                return null;

            } finally {

                inFlight.delete(
                    url,
                );
            }
        })();

    inFlight.set(
        url,
        promise,
    );

    return promise;
}