// =========================================
// NAVIGATION EVENTS
// =========================================

import {
    emitNavigationEvent,
    NAVIGATION_START,
    NAVIGATION_FETCH,
    NAVIGATION_RENDER,
    NAVIGATION_READY,
    NAVIGATION_ERROR,
    NAVIGATION_ABORT,
} from '../../core/navigation-protocol.js';

// =========================================
// START
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

// =========================================
// FETCH
// =========================================

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

// =========================================
// RENDER
// =========================================

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

// =========================================
// READY
// =========================================

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

// =========================================
// ERROR
// =========================================

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

// =========================================
// ABORT
// =========================================

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