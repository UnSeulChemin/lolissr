// =========================================
// NAVIGATION EVENTS
// =========================================

import {
    emitNavigationEvent,
    NAVIGATION_ABORT,
    NAVIGATION_ERROR,
    NAVIGATION_FETCH,
    NAVIGATION_READY,
    NAVIGATION_RENDER,
    NAVIGATION_START,
} from '../../core/navigation-protocol.js';

// =========================================
// INTERNAL
// =========================================

function emit(
    type,
    payload,
)
{
    emitNavigationEvent(
        type,
        payload,
    );
}

// =========================================
// START
// =========================================

export function emitNavigationStart(
    from,
    to,
)
{
    emit(
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
    emit(
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
    emit(
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
    emit(
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
    emit(
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
    emit(
        NAVIGATION_ABORT,
        {
            from,
            to,
        },
    );
}