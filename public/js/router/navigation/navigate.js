// =========================================
// NAVIGATE
// =========================================

import {
    debug,
    debugError,
} from '../../core/debug/debug.js';

import {
    normalizeUrl,
} from '../../core/navigation.js';

import {
    end,
    finish,
    reset,
    start,
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
    renderPage,
} from './navigation-render.js';

import {
    resolvePage,
} from './resolve-page.js';

import {
    validatePageResponse,
} from './validate-page-response.js';

import {
    dispatchRouterLoaded,
} from '../router-events.js';

import {
    runCleanup,
} from '../router-cleanup.js';

import {
    triggerBeforeRouteChange,
    triggerRouteChange,
} from '../router-hooks.js';

import {
    clearController,
    lockRouter,
    navigationState,
    setController,
    unlockRouter,
} from '../router-state.js';

import {
    clearInvalidatedRoute,
    shouldRefreshRoute,
} from '../route-invalidation.js';

import {
    saveScrollPosition,
} from '../route-scroll.js';

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
    | ABORT PREVIOUS NAVIGATION
    |--------------------------------------------------------------------------
    */

    navigationState.controller?.abort();

    /*
    |--------------------------------------------------------------------------
    | REGISTER NAVIGATION
    |--------------------------------------------------------------------------
    */

    const navigationId =
        ++navigationState.navigationId;

    lockRouter();

    const controller =
        new AbortController();

    setController(
        controller,
    );

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

    try
    {
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

        dispatchRouterLoaded(
            target,
        );

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
    }
    catch (error)
    {
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
    }
    finally
    {
        /*
        |--------------------------------------------------------------------------
        | RELEASE CURRENT NAVIGATION
        |--------------------------------------------------------------------------
        */

        if (
            navigationId
            === navigationState.navigationId
        ) {
            clearController();
            unlockRouter();
        }
    }
}