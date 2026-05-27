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

const CACHE_DURATION =
    config.prefetch
        .cacheDuration;

const HOVER_DELAY =
    config.prefetch
        .delay;

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
        url === normalizeUrl(
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

        debug(
            'PREFETCH',
            'cache-hit',
            url,
        );

        return cached;
    }

    // =====================================
    // REUSE
    // =====================================

    const existing =
        getInFlightPrefetch(
            url,
        );

    if (existing) {

        debug(
            'PREFETCH',
            'reuse',
            url,
        );

        return existing;
    }

    // =====================================
    // FETCH
    // =====================================

    const promise =
        (async () =>
        {
            try {

                debug(
                    'PREFETCH',
                    'fetch',
                    url,
                );

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
// BIND
// =========================================

function bindHoverPrefetch()
{
    const links =
        document.querySelectorAll(
            '.ajax-content a[href]',
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
            (
                event,
            ) =>
            {
                if (
                    event.pointerType
                    === ''
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