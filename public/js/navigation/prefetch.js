// =========================================
// PREFETCH SYSTEM (SMART HOVER)
// =========================================

import {
    request,
} from '../core/http.js';

import {
    normalizeUrl,
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
    180;

// =========================================
// STATE
// =========================================

const cache =
    new Map();

const inFlight =
    new Set();

const prefetched =
    new Set();

const hoverTimers =
    new WeakMap();

// =========================================
// CACHE GET
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

    // =====================================
    // CACHE EXPIRED
    // =====================================

    if (
        Date.now()
        - cached.timestamp
        > CACHE_DURATION
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
// PREFETCH CORE
// =========================================

export async function prefetchPage(
    url,
)
{
    const parsed =
        new URL(
            url,
            window.location.origin,
        );

    // =====================================
    // SAME ORIGIN
    // =====================================

    if (
        parsed.origin
        !== window.location.origin
    ) {
        return;
    }

    // =====================================
    // ONLY HTTP
    // =====================================

    if (
        parsed.protocol !== 'http:'
        && parsed.protocol !== 'https:'
    ) {
        return;
    }

    // =====================================
    // SKIP SEARCH PARAMS
    // =====================================

    if (
        parsed.search
    ) {
        return;
    }

    // =====================================
    // SKIP HASH
    // =====================================

    if (
        parsed.hash
    ) {
        return;
    }

    const normalized =
        normalizeUrl(
            url,
        );

    // =====================================
    // ALREADY PREFETCHED
    // =====================================

    if (
        prefetched.has(
            normalized,
        )
    ) {
        return;
    }

    // =====================================
    // CACHE
    // =====================================

    const existing =
        getPrefetchedPage(
            normalized,
        );

    if (existing) {
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
        return;
    }

    inFlight.add(
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
            4000,
        );

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

        // =================================
        // VALID HTML
        // =================================

        if (
            typeof html === 'string'
            && html.length > 0
        ) {

            cache.set(
                normalized,
                {
                    html,
                    timestamp:
                        Date.now(),
                },
            );

            prefetched.add(
                normalized,
            );

            debug(
                'PREFETCH',
                'cached',
                normalized,
            );
        }

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

    } finally {

        clearTimeout(
            timeout,
        );

        inFlight.delete(
            normalized,
        );
    }
}

// =========================================
// HOVER PREFETCH
// =========================================

function bindHoverPrefetch()
{
    document.addEventListener(
        'mouseenter',
        (event) =>
        {
            const target =
                event.target;

            if (
                !(
                    target
                    instanceof Element
                )
            ) {
                return;
            }

            const link =
                target.closest(
                    'a[href]',
                );

            if (
                !(
                    link
                    instanceof HTMLAnchorElement
                )
            ) {
                return;
            }

            // =============================
            // NO AJAX
            // =============================

            if (
                link.dataset.noAjax
                !== undefined
            ) {
                return;
            }

            // =============================
            // NO PREFETCH
            // =============================

            if (
                link.dataset.noPrefetch
                !== undefined
            ) {
                return;
            }

            // =============================
            // NEW TAB
            // =============================

            if (
                link.target
                === '_blank'
            ) {
                return;
            }

            // =============================
            // DOWNLOAD
            // =============================

            if (
                link.hasAttribute(
                    'download',
                )
            ) {
                return;
            }

            // =============================
            // RESET TIMER
            // =============================

            clearTimeout(
                hoverTimers.get(
                    link,
                ),
            );

            // =============================
            // DELAY
            // =============================

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
        true,
    );
}

// =========================================
// GLOBAL
// =========================================

window.__prefetchPage =
    prefetchPage;

// =========================================
// INIT
// =========================================

export function initPrefetch()
{
    bindHoverPrefetch();

    debug(
        'PREFETCH',
        'hover ready',
    );
}