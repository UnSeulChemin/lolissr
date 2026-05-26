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

    if (! link.href) {
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

    // Same page hash

    if (
        url.hash
        && url.pathname
            === window.location.pathname
    ) {
        return true;
    }

    // Blank target

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

    // Opt out

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

// ==================================================
// Prefetch
// ==================================================

function prefetchVisibleLinks()
{
    const links =
        document.querySelectorAll(
            navigationSelector,
        );

    links.forEach(
        (link) =>
        {
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
        },
    );
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

    if (currentController) {

        currentController.abort();
    }

    currentController =
        new AbortController();

    const signal =
        currentController.signal;

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
            signal.aborted
            || navigationId
                !== currentNavigationId
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
                    signal.aborted
                    || navigationId
                        !== currentNavigationId
                ) {
                    return;
                }

                await replaceContent(
                    html,
                );
            },
        );

        if (
            signal.aborted
            || navigationId
                !== currentNavigationId
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

        requestAnimationFrame(
            () =>
            {
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

        console.error(
            '[AJAX NAV]',
            error,
        );

        window.location.assign(
            url.href,
        );
    }
}

// ==================================================
// Click
// ==================================================

function handleClick(
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
            navigationSelector,
        );

    if (
        shouldIgnoreLink(
            link,
        )
    ) {
        return;
    }

    // Native browser behavior

    if (
        event.ctrlKey
        || event.metaKey
        || event.shiftKey
        || event.altKey
        || event.button === 1
    ) {
        return;
    }

    // Same URL

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

    requestAnimationFrame(
        () =>
        {
            prefetchVisibleLinks();
        },
    );
}