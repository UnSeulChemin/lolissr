// =========================================
// RESOLVE PAGE
// =========================================

import {
    debug,
} from '../../core/debug/debug.js';

import {
    end,
    start,
} from '../../core/debug/profiler.js';

import {
    getInFlightPrefetch,
    getPrefetchedPage,
} from '../prefetch/prefetch-cache.js';

import {
    fetchPage,
} from '../router-fetch.js';

// =========================================
// RESOLVE
// =========================================

export async function resolvePage(
    target,
    forceRefresh,
    signal,
)
{
    start(
        'resolve',
    );

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

        start(
            'network',
        );

        const response =
            await fetchPage(
                target,
                {
                    signal,
                },
            );

        end(
            'network',
        );

        end(
            'resolve',
        );

        return response;
    }

    /*
    |--------------------------------------------------------------------------
    | PREFETCH CACHE
    |--------------------------------------------------------------------------
    */

    start(
        'cache',
    );

    const cached =
        getPrefetchedPage(
            target,
        );

    end(
        'cache',
    );

    if (
        cached
    ) {

        debug(
            'ROUTER',
            'cache-hit',
            target,
        );

        end(
            'resolve',
        );

        return cached;
    }

    /*
    |--------------------------------------------------------------------------
    | PREFETCH IN FLIGHT
    |--------------------------------------------------------------------------
    */

    start(
        'prefetch',
    );

    const inFlight =
        getInFlightPrefetch(
            target,
        );

    end(
        'prefetch',
    );

    if (
        inFlight
    ) {

        debug(
            'ROUTER',
            'reuse-prefetch',
            target,
        );

        end(
            'resolve',
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

    start(
        'network',
    );

    const response =
        await fetchPage(
            target,
            {
                signal,
            },
        );

    end(
        'network',
    );

    end(
        'resolve',
    );

    return response;
}