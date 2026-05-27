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

// =========================================
// STATE
// =========================================

const cache =
    new Map();

const inFlight =
    new Map();

const hoverTimers =
    new WeakMap();

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

function isHeaderLink(
    link,
)
{
    return (
        link instanceof HTMLAnchorElement
        && link.classList.contains(
            'nav-link-icon',
        )
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

    debug(
        'PREFETCH',
        'start',
        normalized,
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

        debug(
            'PREFETCH',
            'skip-current-page',
            normalized,
        );

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
        getInFlightPrefetch(
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
    // REQUEST
    // =====================================

    const controller =
        new AbortController();

    const timeout =
        window.setTimeout(
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

                debug(
                    'PREFETCH',
                    'fetch',
                    normalized,
                );

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
// BIND
// =========================================

function bindHoverPrefetch()
{
    const links =
        document.querySelectorAll(
            'a[href]',
        );

    for (const link of links)
    {
        // =================================
        // VALID
        // =================================

        if (
            !(
                link
                instanceof HTMLAnchorElement
            )
        ) {
            continue;
        }

        // =================================
        // IGNORE
        // =================================

        if (
            shouldIgnoreLink(
                link,
            )
        ) {
            continue;
        }

        // =================================
        // HEADER NAV
        // =================================

        if (
            isHeaderLink(
                link,
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
                // =========================
                // ANTI DOUBLE TIMER
                // =========================

                if (
                    hoverTimers.has(
                        link,
                    )
                ) {
                    return;
                }

                const timer =
                    window.setTimeout(
                        () =>
                        {
                            hoverTimers.delete(
                                link,
                            );

                            void prefetchPage(
                                link.href,
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

    debug(
        'PREFETCH',
        'bind-complete',
    );
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