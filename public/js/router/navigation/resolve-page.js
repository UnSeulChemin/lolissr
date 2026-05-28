// =========================================
// RESOLVE PAGE
// =========================================

import {
    getPrefetchedPage,
    getInFlightPrefetch,
} from '../prefetch/prefetch-cache.js';

import {
    fetchPage,
} from '../router-fetch.js';

import {
    debug,
} from '../../core/debug/debug.js';

// =========================================
// RESOLVE
// =========================================

export async function resolvePage(
    target,
    forceRefresh,
    signal,
)
{
    /*
    |--------------------------------------------------------------------------
    | FORCE REFRESH
    |--------------------------------------------------------------------------
    */

    if (
        forceRefresh
    ) {

        debug(
            'ROUTER',
            'force-refresh',
            target,
        );

        return fetchPage(
            target,
            {
                signal,
            },
        );
    }

    /*
    |--------------------------------------------------------------------------
    | PREFETCH CACHE
    |--------------------------------------------------------------------------
    */

    const cached =
        getPrefetchedPage(
            target,
        );

    if (cached) {

        debug(
            'ROUTER',
            'cache-hit',
            target,
        );

        return cached;
    }

    /*
    |--------------------------------------------------------------------------
    | PREFETCH IN FLIGHT
    |--------------------------------------------------------------------------
    */

    const inFlight =
        getInFlightPrefetch(
            target,
        );

    if (inFlight) {

        debug(
            'ROUTER',
            'reuse-prefetch',
            target,
        );

        return inFlight;
    }

    /*
    |--------------------------------------------------------------------------
    | NETWORK FETCH
    |--------------------------------------------------------------------------
    */

    debug(
        'ROUTER',
        'network-fetch',
        target,
    );

    return fetchPage(
        target,
        {
            signal,
        },
    );
}