// =========================================
// PREFETCH SYSTEM
// =========================================

import {
    request,
} from '../core/http.js';

import {
    normalizeUrl,
    shouldIgnoreLink,
} from '../core/navigation.js';

import {
    debug,
    debugError,
} from '../core/debug.js';

// =========================================
// CONFIG
// =========================================

const CACHE_DURATION =
    60000;

const MAX_CACHE_SIZE =
    50;

// =========================================
// GLOBAL STATE
// =========================================

const PREFETCH_STATE =
    window.__PREFETCH_STATE__
    ||= {

        initialized:
            false,

        cache:
            new Map(),

        inFlight:
            new Map(),

        invalidated:
            new Set(),
    };

// =========================================
// STATE
// =========================================

const cache =
    PREFETCH_STATE.cache;

const inFlight =
    PREFETCH_STATE.inFlight;

const invalidated =
    PREFETCH_STATE.invalidated;

// =========================================
// HELPERS
// =========================================

function isExpired(
    entry,
)
{
    return (
        Date.now()
        - entry.timestamp
        > CACHE_DURATION
    );
}

function trimCache()
{
    while (
        cache.size
        > MAX_CACHE_SIZE
    )
    {
        const oldestKey =
            cache.keys()
                .next()
                .value;

        if (!oldestKey) {
            return;
        }

        cache.delete(
            oldestKey,
        );
    }
}

// =========================================
// CACHE
// =========================================

export function getPrefetchedPage(
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

    const cached =
        cache.get(
            url,
        );

    if (!cached) {
        return null;
    }

    if (
        isExpired(
            cached,
        )
    ) {

        cache.delete(
            url,
        );

        return null;
    }

    return {
        type:
            'page',

        page:
            cached.page,
    };
}

// =========================================
// INVALIDATE
// =========================================

export function invalidatePrefetch(
    href,
)
{
    const url =
        normalizeUrl(
            href,
        );

    invalidated.add(
        url,
    );

    cache.delete(
        url,
    );

    inFlight.delete(
        url,
    );

    debug(
        'PREFETCH',
        'invalidate',
        url,
    );
}

// =========================================
// CLEAR
// =========================================

export function clearPrefetchCache()
{
    cache.clear();

    inFlight.clear();

    invalidated.clear();
}

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

                trimCache();

                cache.set(
                    url,
                    {
                        page:
                            response.page,

                        timestamp:
                            Date.now(),
                    },
                );

                invalidated.delete(
                    url,
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

// =========================================
// LINK BINDING
// =========================================

function bindLink(
    link,
)
{
    if (
        !(
            link
            instanceof HTMLAnchorElement
        )
    ) {
        return;
    }

    if (
        shouldIgnoreLink(
            link,
        )
    ) {
        return;
    }

    if (
        link.dataset.prefetchBound
        === 'true'
    ) {
        return;
    }

    link.dataset.prefetchBound =
        'true';

    link.addEventListener(
        'pointerenter',
        (
            event,
        ) =>
        {
            if (
                !event.isTrusted
            ) {
                return;
            }

            void prefetchPage(
                link.href,
            );
        },
        {
            passive:
                true,
        },
    );

    link.addEventListener(
        'pointerdown',
        () =>
        {
            void prefetchPage(
                link.href,
            );
        },
        {
            passive:
                true,
        },
    );

    link.addEventListener(
        'touchstart',
        () =>
        {
            void prefetchPage(
                link.href,
            );
        },
        {
            passive:
                true,

            once:
                true,
        },
    );
}

// =========================================
// BIND
// =========================================

function bindPrefetch()
{
    const links =
        document.querySelectorAll(
            'a[data-prefetch]',
        );

    for (const link of links)
    {
        bindLink(
            link,
        );
    }
}

// =========================================
// INIT
// =========================================

export function initPrefetch()
{
    if (
        PREFETCH_STATE.initialized
    ) {
        return;
    }

    PREFETCH_STATE.initialized =
        true;

    bindPrefetch();

    document.addEventListener(
        'router:loaded',
        bindPrefetch,
    );

    debug(
        'PREFETCH',
        'ready',
    );
}