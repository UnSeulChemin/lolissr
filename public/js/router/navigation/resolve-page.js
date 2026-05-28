// =========================================
// RESOLVE PAGE
// =========================================

import {
    getPrefetchedPage,
    getInFlightPrefetch,
} from '../prefetch.js';

import {
    fetchPage,
} from '../router-fetch.js';

import {
    debug,
} from '../../core/debug/debug.js'

// =========================================
// RESOLVE
// =========================================

export async function resolvePage(
    target,
    forceRefresh,
    signal,
)
{
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

    return fetchPage(
        target,
        {
            signal,
        },
    );
}