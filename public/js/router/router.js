// =========================================
// ROUTER
// =========================================

import {
    normalizeUrl,
    shouldIgnoreLink,
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
    fetchPage,
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
// RESOLVE PAGE
// =========================================

async function resolvePage(
    target,
    forceRefresh,
    signal,
)
{
    /*
    |--------------------------------------------------------------------------
    | FORCE REFRESH
    |--------------------------------------------------------------------------
    */

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

    /*
    |--------------------------------------------------------------------------
    | CACHE
    |--------------------------------------------------------------------------
    */

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

    /*
    |--------------------------------------------------------------------------
    | PREFETCH
    |--------------------------------------------------------------------------
    */

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

    /*
    |--------------------------------------------------------------------------
    | FETCH
    |--------------------------------------------------------------------------
    */

    return fetchPage(
        target,
        {
            signal,
        },
    );
}

// =========================================
// LOCK
// =========================================

function lockRouter()
{
    locked =
        true;
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
// VALIDATE RESPONSE
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

    /*
    |--------------------------------------------------------------------------
    | SAME URL
    |--------------------------------------------------------------------------
    */

    if (
        target === current
        && options.force !== true
    ) {

        return;
    }

    /*
    |--------------------------------------------------------------------------
    | LOCK
    |--------------------------------------------------------------------------
    */

    if (
        locked
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
        ++navigationId;

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
    | EVENTS
    |--------------------------------------------------------------------------
    */

    dispatchRouteStart(
        target,
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

    /*
    |--------------------------------------------------------------------------
    | ABORT PREVIOUS
    |--------------------------------------------------------------------------
    */

    controller?.abort();

    controller =
        new AbortController();

    try {

        /*
        |--------------------------------------------------------------------------
        | BEFORE ROUTE CHANGE
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

        if (forceRefresh) {

            clearInvalidatedRoute(
                target,
            );
        }

        /*
        |--------------------------------------------------------------------------
        | FETCH
        |--------------------------------------------------------------------------
        */

        emitNavigationEvent(
            NAVIGATION_FETCH,
            {
                target,
            },
        );

        const response =
            await resolvePage(
                target,
                forceRefresh,
                controller.signal,
            );

        /*
        |--------------------------------------------------------------------------
        | RACE CONDITION
        |--------------------------------------------------------------------------
        */

        if (
            currentNavigationId
            !== navigationId
        ) {

            emitNavigationEvent(
                NAVIGATION_ABORT,
                {
                    target,
                },
            );

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | VALIDATION
        |--------------------------------------------------------------------------
        */

        validatePageResponse(
            response,
        );

        /*
        |--------------------------------------------------------------------------
        | HISTORY
        |--------------------------------------------------------------------------
        */

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

        /*
        |--------------------------------------------------------------------------
        | TITLE
        |--------------------------------------------------------------------------
        */

        if (
            typeof response.page.title
            === 'string'
        ) {

            document.title =
                response.page.title;
        }

        /*
        |--------------------------------------------------------------------------
        | RENDER
        |--------------------------------------------------------------------------
        */

        emitNavigationEvent(
            NAVIGATION_RENDER,
            {
                target,
            },
        );

        replaceContent(
            response.page.html,
        );

        updateActiveNavigation();

        clearActiveFocus();

        /*
        |--------------------------------------------------------------------------
        | SCROLL
        |--------------------------------------------------------------------------
        */

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

        /*
        |--------------------------------------------------------------------------
        | UNLOCK
        |--------------------------------------------------------------------------
        */

        unlockRouter();

        /*
        |--------------------------------------------------------------------------
        | AFTER
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

                dispatchRouteLoaded(
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

        /*
        |--------------------------------------------------------------------------
        | ABORT
        |--------------------------------------------------------------------------
        */

        if (
            error?.name
            === 'AbortError'
        ) {

            emitNavigationEvent(
                NAVIGATION_ABORT,
                {
                    target,
                },
            );

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | ERROR EVENT
        |--------------------------------------------------------------------------
        */

        emitNavigationEvent(
            NAVIGATION_ERROR,
            {
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