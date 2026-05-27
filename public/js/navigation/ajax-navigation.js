// =========================================
// AJAX NAVIGATION
// =========================================

import {
    normalizeUrl,
    shouldIgnoreLink,
} from '../core/navigation.js';

import {
    getPrefetchedPage,
    getInFlightPrefetch,
    clearPrefetchTimers,
    lockPrefetch,
    unlockPrefetch,
} from './prefetch.js';

import {
    fetchPageHtml,
} from './ajax-fetch.js';

import {
    replaceContent,
} from './ajax-dom.js';

import {
    runPageTransition,
    scrollTop,
} from '../core/page-transition.js';

import {
    debug,
    debugError,
} from '../core/debug.js';

// =========================================
// STATE
// =========================================

let initialized =
    false;

let locked =
    false;

let navigationId =
    0;

let controller =
    null;

// =========================================
// ACTIVE NAVIGATION
// =========================================

function updateActiveNavigation()
{
    const currentPath =
        new URL(
            normalizeUrl(
                location.href,
            ),
        )
            .pathname
            .replace(
                /\/$/,
                '',
            );

    document
        .querySelectorAll(
            '.nav-link-icon',
        )
        .forEach(
            (
                link,
            ) =>
            {
                if (
                    !(
                        link
                        instanceof HTMLAnchorElement
                    )
                ) {
                    return;
                }

                const path =
                    new URL(
                        normalizeUrl(
                            link.href,
                        ),
                    )
                        .pathname
                        .replace(
                            /\/$/,
                            '',
                        );

                const active =
                    path === '/lolissr'
                        ? currentPath === path
                        : (
                            currentPath === path
                            || currentPath.startsWith(
                                path + '/',
                            )
                        );

                link.classList.toggle(
                    'active',
                    active,
                );
            },
        );
}

// =========================================
// EVENTS
// =========================================

function dispatchPageLoaded(
    target,
)
{
    requestAnimationFrame(
        () =>
        {
            document.dispatchEvent(
                new CustomEvent(
                    'ajax:page-loaded',
                    {
                        detail:
                        {
                            href:
                                target,
                        },
                    },
                ),
            );
        },
    );
}

// =========================================
// NAVIGATE
// =========================================

export async function navigateTo(
    href,
    options = {},
)
{
    const target =
        normalizeUrl(
            href,
        );

    const current =
        normalizeUrl(
            location.href,
        );

    // =====================================
    // SAME URL
    // =====================================

    if (
        target === current
        && options.force !== true
    ) {
        return;
    }

    // =====================================
    // LOCK
    // =====================================

    if (
        locked
        && options.force !== true
    ) {
        return;
    }

    locked =
        true;

    clearPrefetchTimers();

    lockPrefetch();

    const currentNavigationId =
        ++navigationId;

    // =====================================
    // ABORT PREVIOUS
    // =====================================

    controller?.abort();

    controller =
        new AbortController();

    try {

        let html =
            getPrefetchedPage(
                target,
            );

        // =================================
        // PREFETCH
        // =================================

        if (!html)
        {
            const inFlight =
                getInFlightPrefetch(
                    target,
                );

            if (inFlight) {

                debug(
                    'AJAX',
                    'reuse-flight',
                    target,
                );

                html =
                    await inFlight;

            } else {

                debug(
                    'AJAX',
                    'fetch',
                    target,
                );

                html =
                    await fetchPageHtml(
                        target,
                        {
                            signal:
                                controller.signal,
                        },
                    );
            }

        } else {

            debug(
                'AJAX',
                'reuse-cache',
                target,
            );
        }

        // =================================
        // RACE CONDITION
        // =================================

        if (
            currentNavigationId
            !== navigationId
        ) {
            return;
        }

        // =================================
        // VALIDATION
        // =================================

        if (
            typeof html
            !== 'string'
        ) {

            throw new Error(
                'Invalid HTML',
            );
        }

        // =================================
        // HISTORY
        // =================================

        if (
            options.updateHistory
            !== false
        ) {

            history.pushState(
                {},
                '',
                target,
            );
        }

        // =================================
        // TRANSITION
        // =================================

        await runPageTransition(
            async () =>
            {
                replaceContent(
                    html,
                );

                updateActiveNavigation();
            },
        );

        // =================================
        // SCROLL
        // =================================

        if (
            options.scrollTop
            !== false
        ) {

            scrollTop();
        }

        // =================================
        // PAGE LOADED
        // =================================

        dispatchPageLoaded(
            target,
        );

        unlockPrefetch();

        debug(
            'AJAX',
            'done',
            target,
        );

    } catch (error) {

        if (
            error?.name
            !== 'AbortError'
        ) {

            debugError(
                'AJAX',
                error,
            );
        }

    } finally {

        if (
            currentNavigationId
            === navigationId
        ) {

            locked =
                false;

            controller =
                null;
        }
    }
}

// =========================================
// CLICK
// =========================================

function handleClick(
    event,
)
{
    if (
        event.defaultPrevented
    ) {
        return;
    }

    if (
        event.button !== 0
    ) {
        return;
    }

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

// =========================================
// POPSTATE
// =========================================

async function handlePopState()
{
    await navigateTo(
        location.href,
        {
            updateHistory:
                false,

            scrollTop:
                false,

            force:
                true,
        },
    );
}

// =========================================
// INIT
// =========================================

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

    updateActiveNavigation();

    debug(
        'AJAX',
        'ready',
    );
}