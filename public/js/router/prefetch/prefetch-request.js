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
    setPrefetchedPage,
} from './prefetch-cache.js';

// =========================================
// IN FLIGHT
// =========================================

export function getInFlightPrefetch(
    href,
)
{
    const url =
        normalizeUrl(
            href,
        );

    if (
        invalidated.has(
            url,
        )
    ) {

        return null;
    }

    return (
        inFlight.get(
            url,
        )
        || null
    );
}

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

    if (
        url
        === normalizeUrl(
            location.href,
        )
    ) {

        return null;
    }

    if (
        invalidated.has(
            url,
        )
    ) {

        return null;
    }

    const cached =
        getPrefetchedPage(
            url,
        );

    if (cached) {

        return cached;
    }

    const existing =
        inFlight.get(
            url,
        );

    if (existing) {

        return existing;
    }

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

                if (
                    response?.type
                    !== 'page'
                ) {

                    return null;
                }

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