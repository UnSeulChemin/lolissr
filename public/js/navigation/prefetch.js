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
    config,
} from '../core/config.js';

import {
    debug,
    debugError,
} from '../core/debug.js';

// =========================================
// CONFIG
// =========================================

const HOVER_DELAY =
    config.prefetch
        .hoverDelay;

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

let bindController =
    null;

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

        debug(
            'PREFETCH',
            'cache-miss',
            url,
        );

        return null;
    }

    debug(
        'PREFETCH',
        'cache-hit',
        url,
    );

    return cached;
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
        cache.get(
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

                cache.set(
                    url,
                    html,
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
// BIND
// =========================================

function bindPrefetch()
{
    bindController?.abort();

    bindController =
        new AbortController();

    const links =
        document.querySelectorAll(
            'a[data-prefetch]',
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
        // POINTER ENTER
        // =================================

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

                clearHoverTimer(
                    link.href,
                );

                const timer =
                    setTimeout(
                        () =>
                        {
                            hoverTimers.delete(
                                normalizeUrl(
                                    link.href,
                                ),
                            );

                            void prefetchPage(
                                link.href,
                            );
                        },
                        HOVER_DELAY,
                    );

                hoverTimers.set(
                    normalizeUrl(
                        link.href,
                    ),
                    timer,
                );
            },
            {
                signal:
                    bindController.signal,

                passive:
                    true,
            },
        );

        // =================================
        // POINTER LEAVE
        // =================================

        link.addEventListener(
            'pointerleave',
            () =>
            {
                clearHoverTimer(
                    link.href,
                );
            },
            {
                signal:
                    bindController.signal,

                passive:
                    true,
            },
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
        'ajax:page-loaded',
        bindPrefetch,
    );

    debug(
        'PREFETCH',
        'ready',
    );
}