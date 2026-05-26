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
} from './prefetch-series.js';

import {
    runPageTransition,
    scrollTop,
} from '../../core/page-transition.js';

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
        link.dataset.ajax
        === 'false'
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
                timeout: 500,
            },
        );

        return;
    }

    window.setTimeout(
        callback,
        150,
    );
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
            navigationSelector,
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

    const url =
        new URL(
            href,
            window.location.origin,
        );

    // ==============================================
    // Abort previous
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
        // Transition
        // ==========================================

        await runPageTransition(
            async () =>
            {
                if (
                    !isNavigationValid(
                        navigationId,
                        signal,
                    )
                ) {
                    return;
                }

                await replaceContent(
                    html,
                    navigationId,
                );
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
                url.pathname
                + url.search,
            );
        }

        // ==========================================
        // Scroll
        // ==========================================

        if (
            options.scrollTop
            !== false
        ) {

            requestAnimationFrame(
                () =>
                {
                    if (
                        navigationId
                        !== currentNavigationId
                    ) {
                        return;
                    }

                    scrollTop(
                        false,
                    );
                },
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
            && (
                error.name
                === 'AbortError'
            )
        ) {
            return;
        }

        console.error(
            '[AJAX NAV]',
            error,
        );

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

    if (
        event.ctrlKey
        || event.metaKey
        || event.shiftKey
        || event.altKey
        || event.button === 1
    ) {
        return;
    }

    if (
        link.href
        === window.location.href
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
            updateHistory: false,
            scrollTop: false,
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

    document.addEventListener(
        'click',
        handleClick,
    );

    window.addEventListener(
        'popstate',
        handlePopState,
    );

    schedulePrefetch(
        () =>
        {
            prefetchVisibleLinks();
        },
    );
}