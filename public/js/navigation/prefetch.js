// ==================================================
// Prefetch Navigation
// ==================================================

import {
    debug,
    debugError,
} from '../core/debug.js';

import {
    config,
} from '../core/config.js';

// ==================================================
// Config
// ==================================================

const AJAX_CONTAINER_SELECTOR =
    '.ajax-content';

const {
    delay: PREFETCH_DELAY,
    cooldown: PREFETCH_COOLDOWN,
    timeout: PREFETCH_TIMEOUT,
    cacheLimit: PREFETCH_CACHE_LIMIT = 50,
} = config.prefetch;

// ==================================================
// Cache
// ==================================================

const prefetchedPages =
    new Map();

const pendingRequests =
    new Map();

const recentPrefetches =
    new Map();

// ==================================================
// State
// ==================================================

let initialized =
    false;

let hoverTimeout =
    null;

// ==================================================
// Selectors
// ==================================================

const linkSelector =
`
a.card-link,
a.dashboard-card,
a.collection-pagination-link,
a[data-prefetch="true"]
`;

// ==================================================
// Helpers
// ==================================================

function normalizeUrl(
    href,
)
{
    const url =
        new URL(
            href,
            window.location.origin,
        );

    let pathname =
        url.pathname;

    if (
        !pathname.endsWith('/')
        && !pathname.includes('.')
    ) {

        pathname += '/';
    }

    return (
        pathname
        + url.search
    );
}

function isValidHtmlResponse(
    html,
)
{
    if (
        typeof html
        !== 'string'
    ) {
        return false;
    }

    return html.includes(
        AJAX_CONTAINER_SELECTOR,
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

function shouldIgnoreLink(
    link,
)
{
    if (
        !(link instanceof HTMLAnchorElement)
    ) {
        return true;
    }

    if (!link.href) {
        return true;
    }

    const url =
        new URL(
            link.href,
            window.location.origin,
        );

    // ==============================================
    // External
    // ==============================================

    if (
        url.origin
        !== window.location.origin
    ) {
        return true;
    }

    // ==============================================
    // Same page hash
    // ==============================================

    if (
        url.hash
        && normalizeUrl(
            url.href,
        ) === normalizeUrl(
            window.location.href,
        )
    ) {
        return true;
    }

    // ==============================================
    // New tab
    // ==============================================

    if (
        link.target
        === '_blank'
    ) {
        return true;
    }

    // ==============================================
    // Download
    // ==============================================

    if (
        link.hasAttribute(
            'download',
        )
    ) {
        return true;
    }

    // ==============================================
    // AJAX opt-out
    // ==============================================

    if (
        link.dataset.noAjax
        !== undefined
    ) {
        return true;
    }

    // ==============================================
    // Static files
    // ==============================================

    if (
        /\.(jpg|jpeg|png|gif|webp|svg|pdf|zip)$/i
            .test(url.pathname)
    ) {
        return true;
    }

    return false;
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

// ==================================================
// Public API
// ==================================================

export function getPrefetchedPage(
    href,
)
{
    return (
        prefetchedPages.get(
            normalizeUrl(
                href,
            ),
        )
        || null
    );
}

// ==================================================
// Prefetch
// ==================================================

export async function prefetchPage(
    href,
)
{
    const normalizedUrl =
        normalizeUrl(
            href,
        );

    // ==============================================
    // Cache
    // ==============================================

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

    // ==============================================
    // Pending request
    // ==============================================

    if (
        pendingRequests.has(
            normalizedUrl,
        )
    ) {
        return;
    }

    // ==============================================
    // Cooldown
    // ==============================================

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

    // ==============================================
    // Controller
    // ==============================================

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

        const response =
            await fetch(
                normalizedUrl,
                {
                    signal:
                        controller.signal,

                    credentials:
                        'same-origin',

                    headers: {
                        'X-Requested-With':
                            'XMLHttpRequest',

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

        if (!response.ok) {

            debug(
                'PREFETCH',
                'http-error',
                response.status,
                normalizedUrl,
            );

            return;
        }

        const html =
            await response.text();

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

// ==================================================
// Hover
// ==================================================

function handlePointerEnter(
    event,
)
{
    const target =
        event.target;

    if (
        !(target instanceof Element)
    ) {
        return;
    }

    const link =
        target.closest(
            linkSelector,
        );

    if (
        shouldIgnoreLink(
            link,
        )
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
    event,
)
{
    const target =
        event.target;

    if (
        !(target instanceof Element)
    ) {
        return;
    }

    const link =
        target.closest(
            linkSelector,
        );

    if (
        shouldIgnoreLink(
            link,
        )
    ) {
        return;
    }

    prefetchPage(
        link.href,
    );
}

// ==================================================
// Init
// ==================================================

export function initPrefetchNavigation()
{
    if (initialized) {
        return;
    }

    initialized =
        true;

    document.addEventListener(
        'pointerenter',
        handlePointerEnter,
        true,
    );

    document.addEventListener(
        'pointerleave',
        handlePointerLeave,
        true,
    );

    document.addEventListener(
        'focusin',
        handleFocus,
    );

    debug(
        'PREFETCH',
        'initialized',
    );
}