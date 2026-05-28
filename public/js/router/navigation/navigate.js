// =========================================
// NAVIGATE
// =========================================

import {
    normalizeUrl,
} from '../../core/navigation.js';

import {
    emitNavigationStart,
    emitNavigationFetch,
    emitNavigationRender,
    emitNavigationReady,
    emitNavigationError,
    emitNavigationAbort,
} from './navigation-events.js';

import {
    resolvePage,
} from './resolve-page.js';

import {
    validatePageResponse,
} from './validate-page-response.js';

import {
    renderPage,
} from './navigation-render.js';

import {
    navigationState,
    lockRouter,
    unlockRouter,
    setController,
    clearController,
} from '../router-state.js';

import {
    runCleanup,
} from '../router-cleanup.js';

import {
    triggerBeforeRouteChange,
    triggerRouteChange,
} from '../router-hooks.js';

import {
    shouldRefreshRoute,
    clearInvalidatedRoute,
} from '../route-invalidation.js';

import {
    saveScrollPosition,
} from '../route-scroll.js';

import {
    dispatchRouterLoaded,
} from '../router-events.js';

import {
    debug,
    debugError,
} from '../../core/debug/debug.js';

// =========================================
// NAVIGATE
// =========================================

export async function navigateTo(
    to,
    options = {},
)
{
    const current =
        normalizeUrl(
            location.href,
        );

    const target =
        normalizeUrl(
            to,
        );

    /*
    |--------------------------------------------------------------------------
    | SAME ROUTE
    |--------------------------------------------------------------------------
    */

    if (
        current === target
        && options.force !== true
    ) {

        debug(
            'ROUTER',
            'same-route',
            target,
        );

        return;
    }

    /*
    |--------------------------------------------------------------------------
    | LOCK
    |--------------------------------------------------------------------------
    */

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

    /*
    |--------------------------------------------------------------------------
    | LOCK ROUTER
    |--------------------------------------------------------------------------
    */

    lockRouter();

    const navigationId =
        ++navigationState.navigationId;

    /*
    |--------------------------------------------------------------------------
    | SAVE SCROLL
    |--------------------------------------------------------------------------
    */

    saveScrollPosition(
        current,
    );

    /*
    |--------------------------------------------------------------------------
    | START EVENT
    |--------------------------------------------------------------------------
    */

    emitNavigationStart(
        current,
        target,
    );

    /*
    |--------------------------------------------------------------------------
    | ABORT PREVIOUS
    |--------------------------------------------------------------------------
    */

    navigationState.controller?.abort();

    const controller =
        new AbortController();

    setController(
        controller,
    );

    try {

        /*
        |--------------------------------------------------------------------------
        | BEFORE HOOKS
        |--------------------------------------------------------------------------
        */

        await triggerBeforeRouteChange(
            {
                from:
                    current,

                to:
                    target,
            },
        );

        /*
        |--------------------------------------------------------------------------
        | CLEANUP
        |--------------------------------------------------------------------------
        */

        runCleanup();

        /*
        |--------------------------------------------------------------------------
        | INVALIDATION
        |--------------------------------------------------------------------------
        */

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

        /*
        |--------------------------------------------------------------------------
        | FETCH
        |--------------------------------------------------------------------------
        */

        emitNavigationFetch(
            current,
            target,
        );

        const response =
            await resolvePage(
                target,
                forceRefresh,
                controller.signal,
            );

        /*
        |--------------------------------------------------------------------------
        | STALE NAVIGATION
        |--------------------------------------------------------------------------
        */

        if (
            navigationId
            !== navigationState.navigationId
        ) {

            emitNavigationAbort(
                current,
                target,
            );

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | VALIDATE
        |--------------------------------------------------------------------------
        */

        validatePageResponse(
            response,
        );

        /*
        |--------------------------------------------------------------------------
        | RENDER EVENT
        |--------------------------------------------------------------------------
        */

        emitNavigationRender(
            current,
            target,
        );

        /*
        |--------------------------------------------------------------------------
        | RENDER PAGE
        |--------------------------------------------------------------------------
        */

        await renderPage(
            current,
            target,
            response,
            options,
        );

        /*
        |--------------------------------------------------------------------------
        | UNLOCK
        |--------------------------------------------------------------------------
        */

        unlockRouter();

        /*
        |--------------------------------------------------------------------------
        | EVENTS
        |--------------------------------------------------------------------------
        */

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

        /*
        |--------------------------------------------------------------------------
        | ABORT
        |--------------------------------------------------------------------------
        */

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

        /*
        |--------------------------------------------------------------------------
        | ERROR
        |--------------------------------------------------------------------------
        */

        emitNavigationError(
            current,
            target,
            error,
        );

        debugError(
            'ROUTER',
            error,
        );

        /*
        |--------------------------------------------------------------------------
        | FALLBACK
        |--------------------------------------------------------------------------
        */

        if (
            options.fallback !== false
        ) {

            window.location.href =
                target;
        }
    } finally {

        /*
        |--------------------------------------------------------------------------
        | CLEAN CONTROLLER
        |--------------------------------------------------------------------------
        */

        if (
            navigationId
            === navigationState.navigationId
        ) {

            clearController();
        }
    }
}