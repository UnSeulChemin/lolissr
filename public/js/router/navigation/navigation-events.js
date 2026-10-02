// =================================================
// ÉVÉNEMENTS DE NAVIGATION
// =================================================

import {
    emitNavigationEvent,
    NAVIGATION_ABORT,
    NAVIGATION_ERROR,
    NAVIGATION_FETCH,
    NAVIGATION_READY,
    NAVIGATION_RENDER,
    NAVIGATION_START,
} from '../../core/navigation-protocol.js';

// =================================================
// INTERNE
// =================================================

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

// =================================================
// DÉMARRAGE
// =================================================

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

// =================================================
// CHARGEMENT
// =================================================

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

// =================================================
// RENDU
// =================================================

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

// =================================================
// PRÊT
// =================================================

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

// =================================================
// ERREUR
// =================================================

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

// =================================================
// ANNULATION
// =================================================

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