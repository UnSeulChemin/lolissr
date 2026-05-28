// =========================================
// ROUTER EVENTS
// =========================================

import {
    emitNavigationEvent,
    NAVIGATION_START,
    NAVIGATION_FETCH,
    NAVIGATION_RENDER,
    NAVIGATION_READY,
    NAVIGATION_ERROR,
    NAVIGATION_ABORT,
} from '../core/navigation-protocol.js';

// =========================================
// ROUTER LOADED
// =========================================

export function dispatchRouterLoaded(
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

// =========================================
// NAVIGATION EVENTS
// =========================================

export function emitNavigationStart(
    from,
    to,
)
{
    emitNavigationEvent(
        NAVIGATION_START,
        {
            from,
            to,
        },
    );
}

export function emitNavigationFetch(
    from,
    to,
)
{
    emitNavigationEvent(
        NAVIGATION_FETCH,
        {
            from,
            to,
        },
    );
}

export function emitNavigationRender(
    from,
    to,
)
{
    emitNavigationEvent(
        NAVIGATION_RENDER,
        {
            from,
            to,
        },
    );
}

export function emitNavigationReady(
    from,
    to,
)
{
    emitNavigationEvent(
        NAVIGATION_READY,
        {
            from,
            to,
        },
    );
}

export function emitNavigationAbort(
    from,
    to,
)
{
    emitNavigationEvent(
        NAVIGATION_ABORT,
        {
            from,
            to,
        },
    );
}

export function emitNavigationError(
    from,
    to,
    error,
)
{
    emitNavigationEvent(
        NAVIGATION_ERROR,
        {
            from,
            to,
            error,
        },
    );
}