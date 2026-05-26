// ==================================================
// Global Prefetch Navigation
// ==================================================

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
// Config
// ==================================================

const PREFETCH_DELAY =
    80;

const PREFETCH_COOLDOWN =
    3000;

const PREFETCH_CACHE_LIMIT =
    50;

const PREFETCH_TIMEOUT =
    8000;

// ==================================================
// Selectors
// ==================================================

const linkSelector =
    `
    a.card-link,
    a.dashboard-card,
    a.nav-link-icon,
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

    return (
        url.pathname
        + url.search
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

function storePrefetchedPage(
    url,
    html,
)
{
    cleanupOldCache();

    prefetchedPages.set(
        url,
        html,
    );
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

    if (
        url.origin
        !== window.location.origin
    ) {
        return true;
    }

    if (
        url.hash
        && url.pathname
            === window.location.pathname
    ) {
        return true;
    }

    if (
        link.target === '_blank'
    ) {
        return true;
    }

    if (
        link.hasAttribute(
            'download',
        )
    ) {
        return true;
    }

    if (
        link.dataset.ajax === 'false'
    ) {
        return true;
    }

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
    const last =
        recentPrefetches.get(
            normalizedUrl,
        );

    return (
        last
        && (
            performance.now()
            - last
        ) < PREFETCH_COOLDOWN
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

    if (
        prefetchedPages.has(
            normalizedUrl,
        )
    ) {
        return;
    }

    if (
        pendingRequests.has(
            normalizedUrl,
        )
    ) {
        return;
    }

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

    const timeout =
        window.setTimeout(
            () =>
            {
                controller.abort();
            },
            PREFETCH_TIMEOUT,
        );

    try {

        const response =
            await fetch(
                normalizedUrl,
                {
                    signal:
                        controller.signal,

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
            error?.name
            === 'AbortError'
        ) {
            return;
        }

        console.error(
            '[PREFETCH]',
            error,
        );

    } finally {

        clearTimeout(
            timeout,
        );

        pendingRequests.delete(
            normalizedUrl,
        );
    }
}

// ==================================================
// Delegated Hover Prefetch
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

function handleTouchStart(
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
// Auto preload next pagination page
// ==================================================

function prefetchNextSeriesPage()
{
    const nextPage =
        document.querySelector(
            '.collection-pagination-link.active + .collection-pagination-link',
        );

    if (
        !(
            nextPage
            instanceof HTMLAnchorElement
        )
    ) {
        return;
    }

    prefetchPage(
        nextPage.href,
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

    document.addEventListener(
        'touchstart',
        handleTouchStart,
        {
            passive: true,
        },
    );

    prefetchNextSeriesPage();

    document.addEventListener(
        'ajax:page-loaded',
        () =>
        {
            queueMicrotask(
                () =>
                {
                    prefetchNextSeriesPage();
                },
            );
        },
    );
}