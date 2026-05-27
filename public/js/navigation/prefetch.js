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

// =========================================
// CACHE
// =========================================

function isExpired(entry)
{
    return (
        Date.now()
        - entry.timestamp
        > CACHE_DURATION
    );
}

export function getPrefetchedPage(url)
{
    const normalized =
        normalizeUrl(url);

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
// PREFETCH
// =========================================

export async function prefetchPage(url)
{
    const normalized =
        normalizeUrl(url);

    // =====================================
    // CURRENT PAGE
    // =====================================

    if (
        normalized
        === normalizeUrl(
            location.href,
        )
    ) {
        return;
    }

    // =====================================
    // CACHE HIT
    // =====================================

    if (
        getPrefetchedPage(
            normalized,
        )
    ) {

        return;
    }

    // =====================================
    // IN FLIGHT
    // =====================================

    if (
        inFlight.has(
            normalized,
        )
    ) {

        return inFlight.get(
            normalized,
        );
    }

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

                            headers: {
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
// HOVER BIND
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

        link.addEventListener(
            'mouseenter',
            () =>
            {
                if (
                    shouldIgnoreLink(
                        link,
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

        link.addEventListener(
            'mouseleave',
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
    bindHoverPrefetch();

    document.addEventListener(
        'ajax:page-loaded',
        () =>
        {
            bindHoverPrefetch();
        },
    );

    debug(
        'PREFETCH',
        'ready',
    );
}