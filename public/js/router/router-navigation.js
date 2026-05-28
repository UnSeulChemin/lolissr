// =========================================
// ROUTER NAVIGATION
// =========================================

import {
    FrontendError,
} from '../core/errors/FrontendError.js';

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
    fetchPage,
} from './router-fetch.js';

import {
    replaceContent,
} from './router-dom.js';

import {
    updateActiveNavigation,
} from './router-active-link.js';

import {
    clearActiveFocus,
} from './router-focus.js';

import {
    routerState,
} from './router-state.js';

import {
    dispatchRouterLoaded,
    emitNavigationStart,
    emitNavigationFetch,
    emitNavigationRender,
    emitNavigationReady,
    emitNavigationAbort,
    emitNavigationError,
} from './router-events.js';

import {
    debug,
    debugError,
} from '../core/debug.js';

// =========================================
// HELPERS
// =========================================

function lockRouter()
{
    routerState.locked =
        true;
}

function unlockRouter()
{
    routerState.locked =
        false;
}

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
    current,
    target,
    options = {},
)
{
    if (
        target === current
        && options.force !== true
    ) {

        return;
    }

    if (
        routerState.locked
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

    const currentNavigationId =
        ++routerState.navigationId;

    saveScrollPosition(
        current,
    );

    emitNavigationStart(
        current,
        target,
    );

    routerState.controller?.abort();

    routerState.controller =
        new AbortController();

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

        if (forceRefresh) {

            clearInvalidatedRoute(
                target,
            );
        }

        emitNavigationFetch(
            current,
            target,
        );

        const response =
            await resolvePage(
                target,
                forceRefresh,
                routerState.controller.signal,
            );

        if (
            currentNavigationId
            !== routerState.navigationId
        ) {

            emitNavigationAbort(
                current,
                target,
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

        emitNavigationRender(
            current,
            target,
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

                emitNavigationReady(
                    current,
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
            === 'AbortError'
        ) {

            emitNavigationAbort(
                current,
                target,
            );

            return;
        }

        emitNavigationError(
            current,
            target,
            error,
        );

        debugError(
            'ROUTER',
            error,
        );
    } finally {

        if (
            currentNavigationId
            === routerState.navigationId
        ) {

            routerState.controller =
                null;
        }
    }
}