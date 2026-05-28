// =========================================
// ROUTER NAVIGATION
// =========================================

import {
    normalizeUrl,
} from '../core/navigation.js';

import {
    FrontendError,
} from '../core/errors/FrontendError.js';

import {
    emitNavigationEvent,
    NAVIGATION_START,
    NAVIGATION_FETCH,
    NAVIGATION_RENDER,
    NAVIGATION_READY,
    NAVIGATION_ERROR,
    NAVIGATION_ABORT,
} from '../core/navigation-protocol.js';

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
    replaceContent,
} from './router-dom.js';

import {
    fetchPage,
} from './router-fetch.js';

import {
    updateActiveNavigation,
} from './router-active-link.js';

import {
    clearActiveFocus,
} from './router-focus.js';

import {
    navigationState,
    lockRouter,
    unlockRouter,
    setController,
    clearController,
} from './router-state.js';

import {
    dispatchRouterLoaded,
} from './router-events.js';

import {
    debug,
    debugError,
} from '../core/debug.js';

// =========================================
// VALIDATION
// =========================================

function validatePageResponse(
    response,
)
{
    if (
        response?.type
        !== 'page'
    ) {

        throw new FrontendError(
            'Réponse page invalide',
            {
                code:
                    'INVALID_PAGE_RESPONSE',
            },
        );
    }

    if (
        typeof response.page?.html
        !== 'string'
    ) {

        throw new FrontendError(
            'HTML page invalide',
            {
                code:
                    'INVALID_PAGE_HTML',
            },
        );
    }
}

// =========================================
// RESOLVE PAGE
// =========================================

async function resolvePage(
    target,
    forceRefresh,
    signal,
)
{
    if (
        forceRefresh
    ) {

        debug(
            'ROUTER',
            'force-refresh',
            target,
        );

        return fetchPage(
            target,
            {
                signal,
            },
        );
    }

    const cached =
        getPrefetchedPage(
            target,
        );

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

    return fetchPage(
        target,
        {
            signal,
        },
    );
}

// =========================================
// NAVIGATE
// =========================================

export async function navigateTo(
    from,
    to,
    options = {},
)
{
    const current =
        normalizeUrl(
            from
            || location.href,
        );

    const target =
        normalizeUrl(
            to,
        );

    if (
        current === target
        && options.force !== true
    ) {

        return;
    }

    if (
        navigationState.locked
        && options.force !== true
    ) {

        debug(
            'ROUTER',
            'locked',
            target,
        );

        return;
    }

    lockRouter();

    const navigationId =
        ++navigationState.navigationId;

    saveScrollPosition(
        current,
    );

    emitNavigationEvent(
        NAVIGATION_START,
        {
            from:
                current,

            to:
                target,
        },
    );

    navigationState.controller?.abort();

    const controller =
        new AbortController();

    setController(
        controller,
    );

    try {

        await triggerBeforeRouteChange(
            {
                from:
                    current,

                to:
                    target,
            },
        );

        runCleanup();

        const forceRefresh =
            shouldRefreshRoute(
                target,
            );

        if (
            forceRefresh
        ) {

            clearInvalidatedRoute(
                target,
            );
        }

        emitNavigationEvent(
            NAVIGATION_FETCH,
            {
                from:
                    current,

                to:
                    target,
            },
        );

        const response =
            await resolvePage(
                target,
                forceRefresh,
                controller.signal,
            );

        if (
            navigationId
            !== navigationState.navigationId
        ) {

            emitNavigationEvent(
                NAVIGATION_ABORT,
                {
                    from:
                        current,

                    to:
                        target,
                },
            );

            return;
        }

        validatePageResponse(
            response,
        );

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

        if (
            typeof response.page.title
            === 'string'
        ) {

            document.title =
                response.page.title;
        }

        emitNavigationEvent(
            NAVIGATION_RENDER,
            {
                from:
                    current,

                to:
                    target,
            },
        );

        replaceContent(
            response.page.html,
        );

        updateActiveNavigation();

        clearActiveFocus();

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

        unlockRouter();

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

                dispatchRouterLoaded(
                    target,
                );

                emitNavigationEvent(
                    NAVIGATION_READY,
                    {
                        from:
                            current,

                        to:
                            target,
                    },
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
            === 'AbortError'
        ) {

            emitNavigationEvent(
                NAVIGATION_ABORT,
                {
                    from:
                        current,

                    to:
                        target,
                },
            );

            return;
        }

        emitNavigationEvent(
            NAVIGATION_ERROR,
            {
                from:
                    current,

                to:
                    target,

                error,
            },
        );

        debugError(
            'ROUTER',
            error,
        );
    } finally {

        if (
            navigationId
            === navigationState.navigationId
        ) {

            clearController();
        }
    }
}