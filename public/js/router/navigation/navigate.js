// =========================================
// NAVIGATE
// =========================================

import {
    normalizeUrl,
} from '../../core/navigation.js';

import {
    debug,
    debugError,
} from '../../core/debug/debug.js';

import {
    end,
    reset,
    start,
    finish,
} from '../../core/debug/profiler.js';

import {
    emitNavigationAbort,
    emitNavigationError,
    emitNavigationFetch,
    emitNavigationReady,
    emitNavigationRender,
    emitNavigationStart,
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
    clearController,
    lockRouter,
    navigationState,
    setController,
    unlockRouter,
} from '../router-state.js';

import {
    runCleanup,
} from '../router-cleanup.js';

import {
    triggerBeforeRouteChange,
    triggerRouteChange,
} from '../router-hooks.js';

import {
    clearInvalidatedRoute,
    shouldRefreshRoute,
} from '../route-invalidation.js';

import {
    saveScrollPosition,
} from '../route-scroll.js';

import {
    dispatchRouterLoaded,
} from '../router-events.js';

// =========================================
// NAVIGATE
// =========================================

export async function navigateTo(
    to,
    options = {},
)
{
    reset();

    start(
        'total',
    );

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

    if (
        options.updateHistory !== false
    ) {

        saveScrollPosition(
            current,
        );
    }

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

        start(
            'cleanup',
        );

        runCleanup();

        end(
            'cleanup',
        );

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
        | RESOLVE PAGE
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

        start(
            'render',
        );

        await renderPage(
            current,
            target,
            response,
            options,
        );

        end(
            'render',
        );

        /*
        |--------------------------------------------------------------------------
        | EVENTS
        |--------------------------------------------------------------------------
        */

        await triggerRouteChange(
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

        unlockRouter();

        emitNavigationReady(
            current,
            target,
        );

        debug(
            'ROUTER',
            'done',
            target,
        );

        finish();

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