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
} from '../../core/debug/debug.js'

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

    emitNavigationStart(
        current,
        target,
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

        validatePageResponse(
            response,
        );

        await renderPage(
            current,
            target,
            response,
            options,
        );

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
            navigationId
            === navigationState.navigationId
        ) {

            clearController();
        }
    }
}