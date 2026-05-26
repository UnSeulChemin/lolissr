// ==================================================
// Global AJAX Navigation
// ==================================================

import {
    fetchPageHtml,
} from './ajax-fetch.js';

import {
    replaceContent,
} from './ajax-dom.js';

import {
    prefetchPage,
} from './prefetch.js';

import {
    runPageTransition,
    scrollTop,
} from '../core/page-transition.js';

import {
    debug,
    debugError,
} from '../core/debug.js';

// ==================================================
// Config
// ==================================================

const PREFETCH_DELAY =
    800;

// ==================================================
// State
// ==================================================

let initialized =
    false;

let currentController =
    null;

let currentNavigationId =
    0;

let transitionLock =
    false;

// ==================================================
// Selectors
// ==================================================

const navigationSelector =
`
a[href]:not(
    [data-no-ajax]
)
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

    // External

    if (
        url.origin
        !== window.location.origin
    ) {
        return true;
    }

    // Same-page hash

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

    // New tab

    if (
        link.target
        === '_blank'
    ) {
        return true;
    }

    // Download

    if (
        link.hasAttribute(
            'download',
        )
    ) {
        return true;
    }

    // Opt-out

    if (
        link.dataset.ajax
        === 'false'
    ) {
        return true;
    }

    // Static files

    if (
        /\.(jpg|jpeg|png|gif|webp|svg|pdf|zip)$/i
            .test(url.pathname)
    ) {
        return true;
    }

    return false;
}

function isNavigationValid(
    navigationId,
    signal,
)
{
    return (
        !signal.aborted
        && navigationId
            === currentNavigationId
    );
}

function schedulePrefetch(
    callback,
)
{
    if (
        'requestIdleCallback'
        in window
    ) {

        window.requestIdleCallback(
            callback,
            {
                timeout: 400,
            },
        );

        return;
    }

    window.setTimeout(
        callback,
        120,
    );
}

// ==================================================
// Active Navigation
// ==================================================

function updateActiveNavigation()
{
    const path =
        normalizeUrl(
            window.location.href,
        );

    const links =
        document.querySelectorAll(
            '.nav-link-icon',
        );

    for (const link of links) {

        if (
            !(link instanceof HTMLAnchorElement)
        ) {
            continue;
        }

        link.classList.remove(
            'active',
        );

        const href =
            normalizeUrl(
                link.href,
            );

        // ==========================================
        // Home
        // ==========================================

        if (
            href === '/lolissr/'
            && path === '/lolissr/'
        ) {

            link.classList.add(
                'active',
            );

            continue;
        }

        // ==========================================
        // Sections
        // ==========================================

        if (
            href !== '/lolissr/'
            && (
                path === href
                || path.startsWith(
                    href,
                )
            )
        ) {

            link.classList.add(
                'active',
            );
        }
    }
}

// ==================================================
// Prefetch
// ==================================================

function prefetchVisibleLinks()
{
    if (transitionLock) {
        return;
    }

    const links =
        document.querySelectorAll(
            `
            a.card-link,
            a.dashboard-card,
            a.collection-pagination-link,
            a[data-prefetch="true"]
            `,
        );

    for (const link of links) {

        if (
            shouldIgnoreLink(
                link,
            )
        ) {
            continue;
        }

        prefetchPage(
            link.href,
        );
    }
}

// ==================================================
// Navigation
// ==================================================

export async function navigateTo(
    href,
    options = {},
)
{
    const navigationId =
        ++currentNavigationId;

    const normalizedTarget =
        normalizeUrl(
            href,
        );

    const normalizedCurrent =
        normalizeUrl(
            window.location.href,
        );

    // Prevent duplicate navigation

    if (
        normalizedTarget
       === normalizedCurrent
    ) {
        return;
    }

    const url =
        new URL(
            href,
            window.location.origin,
        );

    // ==============================================
    // Abort previous navigation
    // ==============================================

    currentController?.abort();

    currentController =
        new AbortController();

    const signal =
        currentController.signal;

    transitionLock =
        true;

    document.body.dataset.ajaxNavigating =
        'true';

    debug(
        'AJAX',
        '➡️ Navigate',
        normalizedTarget,
    );

    try {

        // ==========================================
        // Fetch
        // ==========================================

        const html =
            await fetchPageHtml(
                url.href,
                {
                    signal,
                },
            );

        if (
            !isNavigationValid(
                navigationId,
                signal,
            )
        ) {
            return;
        }

        // ==========================================
        // History
        // ==========================================

        if (
            options.updateHistory
            !== false
        ) {

            window.history.pushState(
                {},
                '',
                normalizedTarget,
            );
        }

        // ==========================================
        // Transition
        // ==========================================

        await runPageTransition(
            () =>
            {
                if (
                    !isNavigationValid(
                        navigationId,
                        signal,
                    )
                ) {
                    return;
                }

                replaceContent(
                    html,
                );

                updateActiveNavigation();
            },
        );

        if (
            !isNavigationValid(
                navigationId,
                signal,
            )
        ) {
            return;
        }

        // ==========================================
        // Scroll
        // ==========================================

        if (
            options.scrollTop
            !== false
        ) {

            scrollTop(
                false,
            );
        }

        // ==========================================
        // Events
        // ==========================================

        document.dispatchEvent(
            new CustomEvent(
                'ajax:page-loaded',
            ),
        );

        // ==========================================
        // Prefetch
        // ==========================================

        schedulePrefetch(
            () =>
            {
                if (
                    navigationId
                    !== currentNavigationId
                ) {
                    return;
                }

                prefetchVisibleLinks();
            },
        );

    } catch (error) {

        if (
            error instanceof Error
            && error.name
                === 'AbortError'
        ) {
            return;
        }

        debugError(
            'AJAX NAV',
            error,
        );

        // Hard fallback

        window.location.assign(
            url.href,
        );

    } finally {

        if (
            navigationId
            === currentNavigationId
        ) {

            transitionLock =
                false;

            delete document.body
                .dataset
                .ajaxNavigating;
        }
    }
}

// ==================================================
// Click
// ==================================================

function handleClick(
    event,
)
{
    if (
        event.defaultPrevented
    ) {
        return;
    }

    // Left click only

    if (
        event.button !== 0
    ) {
        return;
    }

    // Ignore modifiers

    if (
        event.ctrlKey
        || event.metaKey
        || event.shiftKey
        || event.altKey
    ) {
        return;
    }

    const target =
        event.target;

    if (
        !(target instanceof Element)
    ) {
        return;
    }

    const link =
        target.closest(
            navigationSelector,
        );

    if (
        shouldIgnoreLink(
            link,
        )
    ) {
        return;
    }

    event.preventDefault();

    void navigateTo(
        link.href,
    );
}

// ==================================================
// Popstate
// ==================================================

function handlePopState()
{
    void navigateTo(
        window.location.href,
        {
            updateHistory:
                false,

            scrollTop:
                false,
        },
    );
}

// ==================================================
// Init
// ==================================================

export function initAjaxNavigation()
{
    if (initialized) {
        return;
    }

    initialized =
        true;

    updateActiveNavigation();

    document.addEventListener(
        'click',
        handleClick,
    );

    window.addEventListener(
        'popstate',
        handlePopState,
    );

    // Initial delayed prefetch

    window.setTimeout(
        () =>
        {
            prefetchVisibleLinks();
        },
        PREFETCH_DELAY,
    );

    debug(
        'AJAX',
        '✅ Navigation initialized',
    );
}