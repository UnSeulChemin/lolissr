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
    10000;

const HOVER_DELAY =
    120;

const REQUEST_TIMEOUT =
    4000;

const NAVIGATION_LOCK_DURATION =
    1500;

// =========================================
// STATE
// =========================================

const cache =
    new Map();

const inFlight =
    new Map();

const prefetched =
    new Set();

const hoverTimers =
    new WeakMap();

const recentlyNavigated =
    new Set();

let initialized =
    false;

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

// =========================================
// NAVIGATION LOCK
// =========================================

export function markNavigationPrefetch(
    url,
)
{
    const normalized =
        normalizeUrl(
            url,
        );

    recentlyNavigated.add(
        normalized,
    );

    setTimeout(
        () =>
        {
            recentlyNavigated.delete(
                normalized,
            );
        },
        NAVIGATION_LOCK_DURATION,
    );
}

// =========================================
// CLEAR PREFETCH
// =========================================

export function clearPrefetch(
    url,
)
{
    const normalized =
        normalizeUrl(
            url,
        );

    prefetched.delete(
        normalized,
    );

    inFlight.delete(
        normalized,
    );
}

// =========================================
// CACHE
// =========================================

export function getPrefetchedPage(
    url,
)
{
    const normalized =
        normalizeUrl(
            url,
        );

    const cached =
        cache.get(
            normalized,
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
            normalized,
        );

        prefetched.delete(
            normalized,
        );

        return null;
    }

    return cached.html;
}

// =========================================
// IN FLIGHT
// =========================================

export function getInFlightPrefetch(
    url,
)
{
    return inFlight.get(
        normalizeUrl(
            url,
        ),
    );
}

// =========================================
// PREFETCH
// =========================================

export async function prefetchPage(
    url,
)
{
    const normalized =
        normalizeUrl(
            url,
        );

    // =====================================
    // CURRENT PAGE
    // =====================================

    if (
        normalized
        === normalizeUrl(
            location.href,
        )
    ) {
        return null;
    }

    // =====================================
    // RECENT NAVIGATION
    // =====================================

    if (
        recentlyNavigated.has(
            normalized,
        )
    ) {
        return null;
    }

    // =====================================
    // CACHE
    // =====================================

    const cached =
        getPrefetchedPage(
            normalized,
        );

    if (cached) {

        debug(
            'PREFETCH',
            'cache-hit',
            normalized,
        );

        return cached;
    }

    // =====================================
    // IN FLIGHT
    // =====================================

    const existing =
        inFlight.get(
            normalized,
        );

    if (existing) {

        debug(
            'PREFETCH',
            'reuse',
            normalized,
        );

        return existing;
    }

    // =====================================
    // PREFETCHED
    // =====================================

    if (
        prefetched.has(
            normalized,
        )
    ) {
        return null;
    }

    prefetched.add(
        normalized,
    );

    const controller =
        new AbortController();

    const timeout =
        setTimeout(
            () =>
            {
                controller.abort();
            },
            REQUEST_TIMEOUT,
        );

    const promise =
        (async () =>
        {
            try {

                const html =
                    await request(
                        normalized,
                        {
                            responseType:
                                'text',

                            signal:
                                controller.signal,

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
                    || html.length === 0
                ) {

                    prefetched.delete(
                        normalized,
                    );

                    return null;
                }

                cache.set(
                    normalized,
                    {
                        html,
                        timestamp:
                            Date.now(),
                    },
                );

                debug(
                    'PREFETCH',
                    'cached',
                    normalized,
                );

                return html;

            } catch (error) {

                prefetched.delete(
                    normalized,
                );

                if (
                    error?.name
                    !== 'AbortError'
                ) {

                    debugError(
                        'PREFETCH',
                        error,
                    );
                }

                return null;

            } finally {

                clearTimeout(
                    timeout,
                );

                inFlight.delete(
                    normalized,
                );
            }
        })();

    inFlight.set(
        normalized,
        promise,
    );

    return promise;
}

// =========================================
// HOVER
// =========================================

function bindHoverPrefetch()
{
    const links =
        document.querySelectorAll(
            'a[href]',
        );

    for (const link of links)
    {
        if (
            !(
                link
                instanceof HTMLAnchorElement
            )
        ) {
            continue;
        }

        if (
            shouldIgnoreLink(
                link,
            )
        ) {
            continue;
        }

        // =================================
        // HEADER LINKS
        // =================================

        if (
            link.classList.contains(
                'nav-link-icon',
            )
        ) {
            continue;
        }

        // =================================
        // ALREADY BINDED
        // =================================

        if (
            link.dataset.prefetchBound
            === '1'
        ) {
            continue;
        }

        link.dataset.prefetchBound =
            '1';

        // =================================
        // ENTER
        // =================================

        link.addEventListener(
            'pointerenter',
            () =>
            {
                if (
                    document.body.dataset.ajaxNavigating
                    === '1'
                ) {
                    return;
                }

                const normalized =
                    normalizeUrl(
                        link.href,
                    );

                if (
                    recentlyNavigated.has(
                        normalized,
                    )
                ) {
                    return;
                }

                if (
                    prefetched.has(
                        normalized,
                    )
                ) {
                    return;
                }

                clearTimeout(
                    hoverTimers.get(
                        link,
                    ),
                );

                const timer =
                    setTimeout(
                        () =>
                        {
                            hoverTimers.delete(
                                link,
                            );

                            void prefetchPage(
                                normalized,
                            );
                        },
                        HOVER_DELAY,
                    );

                hoverTimers.set(
                    link,
                    timer,
                );
            },
        );

        // =================================
        // LEAVE
        // =================================

        link.addEventListener(
            'pointerleave',
            () =>
            {
                clearTimeout(
                    hoverTimers.get(
                        link,
                    ),
                );

                hoverTimers.delete(
                    link,
                );
            },
        );
    }
}

// =========================================
// INIT
// =========================================

export function initPrefetch()
{
    if (initialized) {
        return;
    }

    initialized =
        true;

    bindHoverPrefetch();

    document.addEventListener(
        'ajax:page-loaded',
        bindHoverPrefetch,
    );

    debug(
        'PREFETCH',
        'ready',
    );
}