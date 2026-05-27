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
    };

// =========================================
// STATE
// =========================================

const cache =
    PREFETCH_STATE.cache;

const inFlight =
    PREFETCH_STATE.inFlight;

// =========================================
// CACHE HELPERS
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
// GET CACHE
// =========================================

export function getPrefetchedPage(
    href,
)
{
    const url =
        normalizeUrl(
            href,
        );

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

    debug(
        'PREFETCH',
        'cache-hit',
        url,
    );

    return cached.html;
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

    cache.delete(
        url,
    );

    inFlight.delete(
        url,
    );
}

// =========================================
// IN FLIGHT
// =========================================

export function getInFlightPrefetch(
    href,
)
{
    return inFlight.get(
        normalizeUrl(
            href,
        ),
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

    // =====================================
    // CURRENT PAGE
    // =====================================

    if (
        url
        === normalizeUrl(
            location.href,
        )
    ) {
        return null;
    }

    // =====================================
    // CACHE
    // =====================================

    const cached =
        getPrefetchedPage(
            url,
        );

    if (cached) {
        return cached;
    }

    // =====================================
    // IN FLIGHT
    // =====================================

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

                const html =
                    await request(
                        url,
                        {
                            responseType:
                                'text',

                            headers:
                            {
                                Accept:
                                    'text/html',

                                'X-Prefetch':
                                    '1',
                            },
                        },
                    );

                if (
                    typeof html
                    !== 'string'
                ) {
                    return null;
                }

                trimCache();

                cache.set(
                    url,
                    {
                        html,

                        timestamp:
                            Date.now(),
                    },
                );

                return html;

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
// BIND LINK
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

    // =====================================
    // HOVER
    // =====================================

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

    // =====================================
    // POINTER DOWN
    // =====================================

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

    // =====================================
    // MOBILE
    // =====================================

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