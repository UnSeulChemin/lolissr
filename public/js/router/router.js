// =========================================
// ROUTER
// =========================================

import {
    normalizeUrl,
    shouldIgnoreLink,
} from '../core/navigation.js';

import {
    getPrefetchedPage,
    getInFlightPrefetch,
} from './prefetch.js';

import {
    shouldRefreshRoute,
    clearInvalidatedRoute,
} from './route-invalidation.js';

import {
    triggerBeforeRouteChange,
    triggerRouteChange,
} from './router-hooks.js';

import {
    runCleanup,
} from './router-cleanup.js';

import {
    saveScrollPosition,
    restoreScrollPosition,
} from './route-scroll.js';

import {
    fetchPageHtml,
} from './router-fetch.js';

import {
    replaceContent,
} from './router-dom.js';

import {
    debug,
    debugError,
} from '../core/debug.js';

// =========================================
// STATE
// =========================================

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
        location.pathname;

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
                    normalizeUrl(
                        link.pathname,
                    );

                const active =
                    path
                    === normalizeUrl(
                        '/lolissr/',
                    )
                        ? currentPath
                            === link.pathname
                        : currentPath.startsWith(
                            link.pathname,
                        );

                link.classList.toggle(
                    'active',
                    active,
                );
            },
        );
}

// =========================================
// CLEAR FOCUS
// =========================================

function clearActiveFocus()
{
    if (
        document.activeElement
        instanceof HTMLElement
    ) {

        document.activeElement.blur();
    }
}

// =========================================
// EVENTS
// =========================================

function dispatchRouteLoaded(
    target,
)
{
    document.dispatchEvent(
        new CustomEvent(
            'router:loaded',
            {
                detail:
                {
                    href:
                        target,
                },
            },
        ),
    );
}

function dispatchRouteStart(
    target,
)
{
    document.dispatchEvent(
        new CustomEvent(
            'router:start',
            {
                detail:
                {
                    href:
                        target,
                },
            },
        ),
    );
}

// =========================================
// FETCH HTML
// =========================================

async function resolvePageHtml(
    target,
    forceRefresh,
    signal,
)
{
    const cached =
        !forceRefresh
            ? getPrefetchedPage(
                target,
            )
            : null;

    if (cached) {

        debug(
            'ROUTER',
            'cache-hit',
            target,
        );

        return cached;
    }

    const inFlight =
        getInFlightPrefetch(
            target,
        );

    if (inFlight) {

        debug(
            'ROUTER',
            'reuse-prefetch',
            target,
        );

        return inFlight;
    }

    return fetchPageHtml(
        target,
        {
            signal,
        },
    );
}

// =========================================
// UNLOCK
// =========================================

function unlockRouter()
{
    locked =
        false;
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

    const currentNavigationId =
        ++navigationId;

    saveScrollPosition(
        current,
    );

    dispatchRouteStart(
        target,
    );

    controller?.abort();

    controller =
        new AbortController();

    try {

        // =================================
        // BEFORE
        // =================================

        await triggerBeforeRouteChange(
            {
                from:
                    current,

                to:
                    target,
            },
        );

        // =================================
        // CLEANUP
        // =================================

        runCleanup();

        // =================================
        // INVALIDATION
        // =================================

        const forceRefresh =
            shouldRefreshRoute(
                target,
            );

        if (forceRefresh) {

            clearInvalidatedRoute(
                target,
            );
        }

        // =================================
        // FETCH
        // =================================

        const html =
            await resolvePageHtml(
                target,
                forceRefresh,
                controller.signal,
            );

        // =================================
        // RACE
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
                'Invalid HTML response',
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
        // DOM SWAP
        // =================================

        replaceContent(
            html,
        );

        updateActiveNavigation();

        clearActiveFocus();

        // =================================
        // SCROLL
        // =================================

        if (
            options.scrollTop
            === true
        ) {

            window.scrollTo(
                0,
                0,
            );

        } else {

            restoreScrollPosition(
                target,
            );
        }

        // =================================
        // INSTANT UNLOCK
        // =================================

        unlockRouter();

        // =================================
        // AFTER
        // =================================

        queueMicrotask(
            () =>
            {
                triggerRouteChange(
                    {
                        from:
                            current,

                        to:
                            target,
                    },
                );

                dispatchRouteLoaded(
                    target,
                );
            },
        );

        debug(
            'ROUTER',
            'done',
            target,
        );

    } catch (error) {

        unlockRouter();

        if (
            error?.name
            !== 'AbortError'
        ) {

            debugError(
                'ROUTER',
                error,
            );
        }

    } finally {

        if (
            currentNavigationId
            === navigationId
        ) {

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

    clearActiveFocus();

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

            force:
                true,
        },
    );
}

// =========================================
// INIT
// =========================================

export function initRouter()
{
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
        'ROUTER',
        'ready',
    );
}