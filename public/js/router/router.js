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
    saveScrollPosition,
    restoreScrollPosition,
} from './route-scroll.js';

import {
    fetchPageHtml,
} from '../navigation/ajax-fetch.js';

import {
    replaceContent,
} from '../navigation/ajax-dom.js';

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
                    link.pathname;

                const active =
                    path === '/lolissr/'
                        ? currentPath === path
                        : currentPath.startsWith(
                            path,
                        );

                link.classList.toggle(
                    'active',
                    active,
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

    const currentNavigationId =
        ++navigationId;

    // =====================================
    // SAVE CURRENT SCROLL
    // =====================================

    saveScrollPosition(
        current,
    );

    // =====================================
    // ABORT PREVIOUS
    // =====================================

    controller?.abort();

    controller =
        new AbortController();

    try {

        // =================================
        // BEFORE ROUTE CHANGE
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

            debug(
                'ROUTER',
                'invalidate-refresh',
                target,
            );
        }

        // =================================
        // CACHE
        // =================================

        const cached =
            !forceRefresh
                ? getPrefetchedPage(
                    target,
                )
                : null;

        // =================================
        // FETCH
        // =================================

        const html =
            cached
            ?? await (
                getInFlightPrefetch(
                    target,
                )
                ?? fetchPageHtml(
                    target,
                    {
                        signal:
                            controller.signal,
                    },
                )
            );

        // =================================
        // RACE CONDITION
        // =====================================

        if (
            currentNavigationId
            !== navigationId
        ) {
            return;
        }

        // =================================
        // VALIDATION
        // =====================================

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
        // =====================================

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
        // =====================================

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
        // =====================================

        if (
            options.restoreScroll
            === true
        ) {

            restoreScrollPosition(
                target,
            );

        } else if (
            options.scrollTop
            !== false
        ) {

            scrollTop();
        }

        // =================================
        // AFTER ROUTE CHANGE
        // =====================================

        await triggerRouteChange(
            {
                from:
                    current,

                to:
                    target,
            },
        );

        debug(
            'ROUTER',
            'done',
            target,
        );

    } catch (error) {

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

            restoreScroll:
                true,

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