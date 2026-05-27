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

    // =====================================
    // INVALIDATED
    // =====================================

    if (
        invalidated.has(
            url,
        )
    ) {

        debug(
            'PREFETCH',
            'blocked-invalidated',
            url,
        );

        return null;
    }

    const cached =
        cache.get(
            url,
        );

    if (!cached) {
        return null;
    }

    // =====================================
    // EXPIRED
    // =====================================

    if (
        isExpired(
            cached,
        )
    ) {

        cache.delete(
            url,
        );

        debug(
            'PREFETCH',
            'cache-expired',
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
// CLEAR ALL
// =========================================

export function clearPrefetchCache()
{
    cache.clear();

    inFlight.clear();

    invalidated.clear();

    debug(
        'PREFETCH',
        'clear-all',
    );
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

    // =====================================
    // INVALIDATED
    // =====================================

    if (
        invalidated.has(
            url,
        )
    ) {

        debug(
            'PREFETCH',
            'blocked-inflight',
            url,
        );

        return null;
    }

    return inFlight.get(
        url,
    ) || null;
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
    // INVALIDATED
    // =====================================

    if (
        invalidated.has(
            url,
        )
    ) {

        debug(
            'PREFETCH',
            'skip-invalidated',
            url,
        );

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

        debug(
            'PREFETCH',
            'reuse-inflight',
            url,
        );

        return existing;
    }

    debug(
        'PREFETCH',
        'fetch',
        url,
    );

    // =====================================
    // FETCH
    // =====================================

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

                                'Cache-Control':
                                    'no-cache',
                            },
                        },
                    );

                // =============================
                // VALIDATION
                // =============================

                if (
                    typeof html
                    !== 'string'
                ) {

                    debug(
                        'PREFETCH',
                        'invalid-html',
                        url,
                    );

                    return null;
                }

                // =============================
                // SAVE CACHE
                // =============================

                trimCache();

                cache.set(
                    url,
                    {
                        html,

                        timestamp:
                            Date.now(),
                    },
                );

                // =============================
                // REFRESHED
                // =============================

                invalidated.delete(
                    url,
                );

                debug(
                    'PREFETCH',
                    'success',
                    url,
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