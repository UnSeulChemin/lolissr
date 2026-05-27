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

        navigationLocked:
            false,

        mouseMoved:
            false,
    };

// =========================================
// CONFIG
// =========================================

const CACHE_DURATION =
    config.prefetch.cacheDuration;

const HOVER_DELAY =
    config.prefetch.delay;

// =========================================
// STATE
// =========================================

const cache =
    PREFETCH_STATE.cache;

const inFlight =
    PREFETCH_STATE.inFlight;

const hoverTimers =
    new WeakMap();

const activeTimers =
    new Set();

// =========================================
// DEBUG
// =========================================

debug(
    'PREFETCH',
    'module-load',
);

// =========================================
// MOUSE TRACK
// =========================================

window.addEventListener(
    'mousemove',
    () =>
    {
        PREFETCH_STATE.mouseMoved =
            true;
    },
    {
        passive:
            true,
    },
);

// =========================================
// NAVIGATION LOCK
// =========================================

export function lockPrefetch()
{
    PREFETCH_STATE.navigationLocked =
        true;

    PREFETCH_STATE.mouseMoved =
        false;

    debug(
        'PREFETCH',
        'lock',
    );
}

export function unlockPrefetch()
{
    window.setTimeout(
        () =>
        {
            PREFETCH_STATE.navigationLocked =
                false;

            debug(
                'PREFETCH',
                'unlock',
            );
        },
        300,
    );
}

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

function createCacheKey(
    url,
)
{
    return normalizeUrl(
        url,
    );
}

// =========================================
// CLEAR TIMERS
// =========================================

export function clearPrefetchTimers()
{
    for (const timer of activeTimers)
    {
        clearTimeout(
            timer,
        );
    }

    activeTimers.clear();
}

// =========================================
// CACHE
// =========================================

export function getPrefetchedPage(
    url,
)
{
    const key =
        createCacheKey(
            url,
        );

    const cached =
        cache.get(
            key,
        );

    if (!cached) {

        debug(
            'PREFETCH',
            'cache-miss',
            key,
        );

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
            key,
        );

        debug(
            'PREFETCH',
            'cache-expired',
            key,
        );

        return null;
    }

    debug(
        'PREFETCH',
        'cache-hit',
        key,
    );

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
        createCacheKey(
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
    const key =
        createCacheKey(
            href,
        );

    debug(
        'PREFETCH',
        'prefetch-call',
        key,
    );

    // =====================================
    // CURRENT PAGE
    // =====================================

    if (
        key
        === createCacheKey(
            location.href,
        )
    ) {

        debug(
            'PREFETCH',
            'skip-current',
            key,
        );

        return null;
    }

    // =====================================
    // CACHE
    // =====================================

    const cached =
        getPrefetchedPage(
            key,
        );

    if (cached) {

        debug(
            'PREFETCH',
            'reuse-cache',
            key,
        );

        return cached;
    }

    // =====================================
    // IN FLIGHT
    // =====================================

    const existing =
        getInFlightPrefetch(
            key,
        );

    if (existing) {

        debug(
            'PREFETCH',
            'reuse-flight',
            key,
        );

        return existing;
    }

    // =====================================
    // FETCH
    // =====================================

    debug(
        'PREFETCH',
        'real-fetch',
        key,
    );

    const promise =
        (async () =>
        {
            try {

                const html =
                    await request(
                        key,
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

                    debug(
                        'PREFETCH',
                        'invalid-html',
                        key,
                    );

                    return null;
                }

                cache.set(
                    key,
                    {
                        html,
                        timestamp:
                            Date.now(),
                    },
                );

                debug(
                    'PREFETCH',
                    'cache-set',
                    key,
                );

                debug(
                    'PREFETCH',
                    'cache-size',
                    cache.size,
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
                    key,
                );
            }
        })();

    inFlight.set(
        key,
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
        === '1'
    ) {
        return;
    }

    link.dataset.prefetchBound =
        '1';

    debug(
        'PREFETCH',
        'bind',
        link.href,
    );

    // =====================================
    // ENTER
    // =====================================

    link.addEventListener(
        'mouseenter',
        () =>
        {
            debug(
                'PREFETCH',
                'mouseenter',
                link.href,
            );

            // =============================
            // AUTO HOVER AFTER SPA NAV
            // =============================

            if (
                PREFETCH_STATE.navigationLocked
                && !PREFETCH_STATE.mouseMoved
            ) {

                debug(
                    'PREFETCH',
                    'blocked-auto-hover',
                    link.href,
                );

                return;
            }

            clearTimeout(
                hoverTimers.get(
                    link,
                ),
            );

            const timer =
                window.setTimeout(
                    () =>
                    {
                        activeTimers.delete(
                            timer,
                        );

                        debug(
                            'PREFETCH',
                            'timer-fire',
                            link.href,
                        );

                        void prefetchPage(
                            link.href,
                        );
                    },
                    HOVER_DELAY,
                );

            activeTimers.add(
                timer,
            );

            hoverTimers.set(
                link,
                timer,
            );
        },
    );

    // =====================================
    // LEAVE
    // =====================================

    link.addEventListener(
        'mouseleave',
        () =>
        {
            const timer =
                hoverTimers.get(
                    link,
                );

            clearTimeout(
                timer,
            );

            activeTimers.delete(
                timer,
            );

            hoverTimers.delete(
                link,
            );
        },
    );
}

// =========================================
// BIND
// =========================================

function bindHoverPrefetch()
{
    const links =
        document.querySelectorAll(
            'a[data-prefetch]',
        );

    debug(
        'PREFETCH',
        'found',
        links.length,
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

        debug(
            'PREFETCH',
            'already-init',
        );

        return;
    }

    PREFETCH_STATE.initialized =
        true;

    bindHoverPrefetch();

    document.addEventListener(
        'ajax:page-loaded',
        () =>
        {
            debug(
                'PREFETCH',
                'page-loaded',
            );

            requestAnimationFrame(
                () =>
                {
                    bindHoverPrefetch();
                },
            );
        },
    );

    debug(
        'PREFETCH',
        'ready',
    );
}