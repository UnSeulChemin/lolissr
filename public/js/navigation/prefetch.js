// =========================================
// PREFETCH NAVIGATION
// =========================================

import {
    request,
} from '../core/http.js';

import {
    normalizeUrl,
    shouldIgnoreLink,
} from '../core/navigation.js';

import {
    delegate,
} from '../core/dom.js';

import {
    debug,
    debugError,
} from '../core/debug.js';

import {
    config,
} from '../core/config.js';

// =========================================
// Config
// =========================================

const AJAX_CONTAINER_SELECTOR =
    '.ajax-content';

const {
    delay: PREFETCH_DELAY,
    cooldown: PREFETCH_COOLDOWN,
    timeout: PREFETCH_TIMEOUT,
    cacheLimit:
        PREFETCH_CACHE_LIMIT = 50,
} = config.prefetch;

// =========================================
// State
// =========================================

const prefetchedPages =
    new Map();

const pendingRequests =
    new Map();

const recentPrefetches =
    new Map();

let initialized =
    false;

let hoverTimeout =
    null;

// =========================================
// Selectors
// =========================================

const linkSelector =
`
a.card-link,
a.dashboard-card,
a.collection-pagination-link,
a[data-prefetch="true"]
`;

// =========================================
// Helpers
// =========================================

function isValidHtmlResponse(
    html,
)
{
    return (
        typeof html
            === 'string'
        && html.includes(
            AJAX_CONTAINER_SELECTOR,
        )
    );
}

function cleanupOldCache()
{
    while (
        prefetchedPages.size
        >= PREFETCH_CACHE_LIMIT
    ) {

        const oldestKey =
            prefetchedPages
                .keys()
                .next()
                .value;

        if (!oldestKey) {
            break;
        }

        prefetchedPages.delete(
            oldestKey,
        );
    }
}

function isRecentlyPrefetched(
    normalizedUrl,
)
{
    const lastPrefetch =
        recentPrefetches.get(
            normalizedUrl,
        );

    return (
        typeof lastPrefetch
            === 'number'
        && (
            performance.now()
            - lastPrefetch
        ) < PREFETCH_COOLDOWN
    );
}

function storePrefetchedPage(
    url,
    html,
)
{
    if (
        !isValidHtmlResponse(
            html,
        )
    ) {

        debug(
            'PREFETCH',
            'invalid-html',
            url,
        );

        return;
    }

    cleanupOldCache();

    prefetchedPages.set(
        url,
        html,
    );

    debug(
        'PREFETCH',
        'cached',
        url,
    );
}

// =========================================
// Public API
// =========================================

export function getPrefetchedPage(
    href,
)
{
    return (
        prefetchedPages.get(
            normalizeUrl(href),
        )
        || null
    );
}

// =========================================
// Prefetch
// =========================================

export async function prefetchPage(
    href,
)
{
    const normalizedUrl =
        normalizeUrl(href);

    // Cache

    if (
        prefetchedPages.has(
            normalizedUrl,
        )
    ) {

        debug(
            'PREFETCH',
            'cache-hit',
            normalizedUrl,
        );

        return;
    }

    // Pending

    if (
        pendingRequests.has(
            normalizedUrl,
        )
    ) {
        return;
    }

    // Cooldown

    if (
        isRecentlyPrefetched(
            normalizedUrl,
        )
    ) {
        return;
    }

    recentPrefetches.set(
        normalizedUrl,
        performance.now(),
    );

    const controller =
        new AbortController();

    pendingRequests.set(
        normalizedUrl,
        controller,
    );

    const timeoutId =
        window.setTimeout(
            () =>
            {
                controller.abort();
            },
            PREFETCH_TIMEOUT,
        );

    try {

        debug(
            'PREFETCH',
            'fetch',
            normalizedUrl,
        );

        const html =
            await request(
                normalizedUrl,
                {
                    responseType:
                        'text',

                    signal:
                        controller.signal,

                    headers: {
                        'X-Partial':
                            'true',

                        'X-Prefetch':
                            'true',

                        'Purpose':
                            'prefetch',

                        'Accept':
                            'text/html',
                    },
                },
            );

        storePrefetchedPage(
            normalizedUrl,
            html,
        );

    } catch (error) {

        if (
            error instanceof Error
            && error.name
                === 'AbortError'
        ) {

            debug(
                'PREFETCH',
                'aborted',
                normalizedUrl,
            );

            return;
        }

        debugError(
            'PREFETCH',
            error,
        );

    } finally {

        clearTimeout(
            timeoutId,
        );

        pendingRequests.delete(
            normalizedUrl,
        );
    }
}

// =========================================
// Events
// =========================================

function handlePointerEnter(
    _,
    link,
)
{
    if (
        shouldIgnoreLink(link)
    ) {
        return;
    }

    clearTimeout(
        hoverTimeout,
    );

    hoverTimeout =
        window.setTimeout(
            () =>
            {
                prefetchPage(
                    link.href,
                );
            },
            PREFETCH_DELAY,
        );
}

function handlePointerLeave()
{
    clearTimeout(
        hoverTimeout,
    );
}

function handleFocus(
    _,
    link,
)
{
    if (
        shouldIgnoreLink(link)
    ) {
        return;
    }

    prefetchPage(
        link.href,
    );
}

// =========================================
// Init
// =========================================

export function initPrefetchNavigation()
{
    if (initialized) {
        return;
    }

    initialized =
        true;

    delegate(
        document,
        'pointerenter',
        linkSelector,
        handlePointerEnter,
    );

    delegate(
        document,
        'focusin',
        linkSelector,
        handleFocus,
    );

    document.addEventListener(
        'pointerleave',
        handlePointerLeave,
        true,
    );

    debug(
        'PREFETCH',
        'initialized',
    );
}