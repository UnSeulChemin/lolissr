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

    debug(
        'PREFETCH',
        'cache-check',
        {
            url,
            hasCache:
                Boolean(
                    cached,
                ),
        },
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

    const request =
        inFlight.get(
            url,
        );

    debug(
        'PREFETCH',
        'inflight-check',
        {
            url,

            hasInFlight:
                Boolean(
                    request,
                ),
        },
    );

    return request;
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

    debug(
        'PREFETCH',
        'start',
        url,
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

        debug(
            'PREFETCH',
            'skip-current',
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

        debug(
            'PREFETCH',
            'reuse-cache',
            url,
        );

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
            'reuse-flight',
            url,
        );

        return existing;
    }

    // =====================================
    // FETCH
    // =====================================

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

                debug(
                    'PREFETCH',
                    'cached',
                    {
                        url,

                        cacheSize:
                            cache.size,
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

                debug(
                    'PREFETCH',
                    'inflight-delete',
                    url,
                );

                inFlight.delete(
                    url,
                );
            }
        })();

    debug(
        'PREFETCH',
        'inflight-set',
        url,
    );

    inFlight.set(
        url,
        promise,
    );

    return promise;
}

// =========================================
// POINTER ENTER
// =========================================

function handlePointerEnter(
    link,
)
{
    const url =
        normalizeUrl(
            link.href,
        );

    debug(
        'PREFETCH',
        'pointer-enter',
        url,
    );

    // =====================================
    // CACHE
    // =====================================

    if (
        getPrefetchedPage(
            url,
        )
    ) {

        debug(
            'PREFETCH',
            'skip-cache',
            url,
        );

        return;
    }

    // =====================================
    // IN FLIGHT
    // =====================================

    if (
        inFlight.has(
            url,
        )
    ) {

        debug(
            'PREFETCH',
            'skip-flight',
            url,
        );

        return;
    }

    // =====================================
    // DIRECT PREFETCH
    // =====================================

    debug(
        'PREFETCH',
        'hover-trigger',
        url,
    );

    void prefetchPage(
        url,
    );
}

// =========================================
// POINTER LEAVE
// =========================================

function handlePointerLeave(
    link,
)
{
    debug(
        'PREFETCH',
        'pointer-leave',
        normalizeUrl(
            link.href,
        ),
    );
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

    // =====================================
    // ALREADY BOUND
    // =====================================

    if (
        link.dataset.prefetchBound
        === 'true'
    ) {

        debug(
            'PREFETCH',
            'already-bound',
            link.href,
        );

        return;
    }

    link.dataset.prefetchBound =
        'true';

    debug(
        'PREFETCH',
        'bind-link',
        normalizeUrl(
            link.href,
        ),
    );

    // =====================================
    // POINTER ENTER
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

            handlePointerEnter(
                link,
            );
        },
        {
            passive:
                true,
        },
    );

    // =====================================
    // POINTER LEAVE
    // =====================================

    link.addEventListener(
        'pointerleave',
        () =>
        {
            handlePointerLeave(
                link,
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
            debug(
                'PREFETCH',
                'touch-prefetch',
                normalizeUrl(
                    link.href,
                ),
            );

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

    debug(
        'PREFETCH',
        'bind-start',
        links.length,
    );

    for (const link of links)
    {
        bindLink(
            link,
        );
    }

    debug(
        'PREFETCH',
        'bind-done',
        links.length,
    );
}

// =========================================
// INIT
// =========================================

export function initPrefetch()
{
    if (
        PREFETCH_STATE.initialized
    ) {

        debug(
            'PREFETCH',
            'already-init',
        );

        return;
    }

    PREFETCH_STATE.initialized =
        true;

    bindPrefetch();

    document.addEventListener(
        'router:loaded',
        (
            event,
        ) =>
        {
            debug(
                'PREFETCH',
                'router-loaded',
                event.detail.href,
            );

            bindPrefetch();
        },
    );

    debug(
        'PREFETCH',
        'ready',
    );
}