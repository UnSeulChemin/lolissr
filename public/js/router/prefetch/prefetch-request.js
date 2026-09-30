// =========================================
// PREFETCH REQUEST
// =========================================

import {
    config,
} from '../../core/config.js';

import {
    debug,
    debugError,
} from '../../core/debug/debug.js';

import {
    request,
} from '../../core/http.js';

import {
    normalizeCacheKey,
} from '../../core/navigation.js';

import {
    getInFlightPrefetch,
    getPrefetchedPage,
    setPrefetchedPage,
} from './prefetch-cache.js';

import {
    inFlight,
    invalidated,
} from './prefetch-state.js';

// =========================================
// PREFETCH
// =========================================

const MAX_CONCURRENT_PREFETCHES = 3;
let activeRequests = 0;

export async function prefetchPage(href)
{
    if (! config.prefetch.enabled)
    {
        return null;
    }

    const url = normalizeCacheKey(
        href,
    );

    /*
    |--------------------------------------------------------------------------
    | CURRENT PAGE
    |--------------------------------------------------------------------------
    */

    if (url === normalizeCacheKey(location.href))
    {
        return null;
    }

    /*
    |--------------------------------------------------------------------------
    | INVALIDATED
    |--------------------------------------------------------------------------
    */

    if (invalidated.has(url))
    {
        return null;
    }

    /*
    |--------------------------------------------------------------------------
    | CACHE
    |--------------------------------------------------------------------------
    */

    const cached = getPrefetchedPage(
        url,
    );

    if (cached)
    {
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

    const existing = getInFlightPrefetch(
        url,
    );

    if (existing)
    {
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

    // Speculative requests can be skipped; actual navigation still fetches on demand.
    if (activeRequests >= MAX_CONCURRENT_PREFETCHES) return null;
    activeRequests++;
    const controller = new AbortController();

    let promise;

    promise = (async () =>
    {
        try
        {
            const response = await request(
                url,
                {
                    timeout: config.prefetch.timeout,

                    headers: {
                        'X-Page-Format': 'fragment',
                        Accept: 'application/json',
                        'X-Prefetch': 'true',
                        'Cache-Control': 'no-cache',
                    },

                    signal: controller.signal,
                },
            );

            /*
            |--------------------------------------------------------------------------
            | VALIDATION
            |--------------------------------------------------------------------------
            */

            if (response?.type !== 'page')
            {
                debug(
                    'PREFETCH',
                    'invalid-response',
                    url,
                );

                return null;
            }

            /*
            |--------------------------------------------------------------------------
            | INVALIDATED DURING REQUEST
            |--------------------------------------------------------------------------
            */

            if (controller.signal.aborted || invalidated.has(url))
            {
                debug(
                    'PREFETCH',
                    'skip-invalidated',
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
        }
        catch (error)
        {
            if (error?.name === 'AbortError')
            {
                debug(
                    'PREFETCH',
                    'aborted',
                    url,
                );

                return null;
            }

            debugError(
                'PREFETCH',
                error,
            );

            return null;
        }
        finally
        {
            activeRequests--;
            const currentEntry = inFlight.get(
                url,
            );

            if (currentEntry?.promise === promise)
            {
                inFlight.delete(
                    url,
                );
            }
        }
    })();

    inFlight.set(
        url,
        {
            promise,
            controller,
        },
    );

    return promise;
}
