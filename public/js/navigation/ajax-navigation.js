// =========================================
// AJAX NAVIGATION
// =========================================

import {
    $$,
} from '../core/dom.js';

import {
    normalizeUrl,
    shouldIgnoreLink,
} from '../core/navigation.js';

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

import {
    config,
} from '../core/config.js';

// =========================================
// Config
// =========================================

const PREFETCH_DELAY =
    800;

const navigationSelector =
`
a[href]:not(
    [data-no-ajax]
)
`;

// =========================================
// State
// =========================================

let initialized =
    false;

let currentController =
    null;

let currentNavigationId =
    0;

let transitionLock =
    false;

// =========================================
// Helpers
// =========================================

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

// =========================================
// Active Navigation
// =========================================

function updateActiveNavigation()
{
    const currentPath =
        normalizeUrl(
            window.location.pathname,
        );

    const links =
        $$(
            '.nav-link-icon',
        );

    for (const link of links)
    {
        if (
            !(link instanceof HTMLAnchorElement)
        ) {
            continue;
        }

        link.classList.remove(
            'active',
        );

        const linkPath =
            normalizeUrl(
                link.pathname,
            );

        // =====================================
        // Home
        // =====================================

        if (
            linkPath === config.baseUrl
            && currentPath
                === config.baseUrl
        ) {

            link.classList.add(
                'active',
            );

            continue;
        }

        // =====================================
        // Sections
        // =====================================

        if (
            linkPath !== config.baseUrl
            && currentPath.startsWith(
                linkPath,
            )
        ) {

            link.classList.add(
                'active',
            );
        }
    }
}

// =========================================
// Prefetch
// =========================================

function prefetchVisibleLinks()
{
    if (transitionLock) {
        return;
    }

    const links =
        $$(
            `
            a.card-link,
            a.dashboard-card,
            a.collection-pagination-link,
            a[data-prefetch="true"]
            `,
        );

    for (const link of links)
    {
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

// =========================================
// Navigation
// =========================================

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

    // =====================================
    // Prevent duplicate navigation
    // =====================================

    if (
        normalizedTarget
        === normalizedCurrent
    ) {

        debug(
            'AJAX',
            'skip-same-url',
            normalizedTarget,
        );

        return;
    }

    const url =
        new URL(
            href,
            window.location.origin,
        );

    // =====================================
    // Abort previous navigation
    // =====================================

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
        'navigate',
        normalizedTarget,
    );

    try {

        // =================================
        // Fetch
        // =================================

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

        // =================================
        // History
        // =================================

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

        // =================================
        // Transition
        // =================================

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

        // =================================
        // Scroll
        // =================================

        if (
            options.scrollTop
            !== false
        ) {

            scrollTop(
                false,
            );
        }

        // =================================
        // Events
        // =================================

        document.dispatchEvent(
            new CustomEvent(
                'ajax:page-loaded',
            ),
        );

        // =================================
        // Prefetch
        // =================================

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

        debug(
            'AJAX',
            'done',
            normalizedTarget,
        );

    } catch (error) {

        if (
            error instanceof Error
            && error.name
                === 'AbortError'
        ) {

            debug(
                'AJAX',
                'aborted',
                normalizedTarget,
            );

            return;
        }

        debugError(
            'AJAX',
            error,
        );

        // =================================
        // Hard fallback
        // =================================

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

// =========================================
// Events
// =========================================

function handleClick(
    event,
)
{
    if (
        event.defaultPrevented
        || event.button !== 0
        || event.ctrlKey
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

// =========================================
// Init
// =========================================

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

    window.setTimeout(
        () =>
        {
            prefetchVisibleLinks();
        },
        PREFETCH_DELAY,
    );

    debug(
        'AJAX',
        'initialized',
    );
}