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

const HOVER_DELAY =
    220;

const CACHE_DURATION =
    10000;

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

const hoverTimers =
    new Map();

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

    const expired =
        Date.now()
        - cached.timestamp
        > CACHE_DURATION;

    if (expired) {

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
                                'X-Prefetch':
                                    '1',

                                Accept:
                                    'text/html',
                            },
                        },
                    );

                if (
                    typeof html
                    !== 'string'
                ) {
                    return null;
                }

                // =============================
                // CACHE
                // =============================

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
// TIMERS
// =========================================

function clearHoverTimer(
    href,
)
{
    const url =
        normalizeUrl(
            href,
        );

    const timer =
        hoverTimers.get(
            url,
        );

    if (!timer) {
        return;
    }

    clearTimeout(
        timer,
    );

    hoverTimers.delete(
        url,
    );
}

// =========================================
// LINK
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
        return;
    }

    link.dataset.prefetchBound =
        'true';

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

            const url =
                normalizeUrl(
                    link.href,
                );

            // =============================
            // CACHE
            // =============================

            if (
                getPrefetchedPage(
                    url,
                )
            ) {
                return;
            }

            // =============================
            // IN FLIGHT
            // =============================

            if (
                inFlight.has(
                    url,
                )
            ) {
                return;
            }

            clearHoverTimer(
                url,
            );

            const timer =
                setTimeout(
                    () =>
                    {
                        hoverTimers.delete(
                            url,
                        );

                        void prefetchPage(
                            url,
                        );
                    },
                    HOVER_DELAY,
                );

            hoverTimers.set(
                url,
                timer,
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
            clearHoverTimer(
                link.href,
            );
        },
        {
            passive:
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

    debug(
        'PREFETCH',
        'bind',
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