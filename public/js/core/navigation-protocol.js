// =========================================
// NAVIGATION PROTOCOL
// =========================================

import {
    debug,
} from '../core/debug/debug.js';

// =========================================
// EVENTS
// =========================================

export const NAVIGATION_START =
    'navigation:start';

export const NAVIGATION_FETCH =
    'navigation:fetch';

export const NAVIGATION_RENDER =
    'navigation:render';

export const NAVIGATION_READY =
    'navigation:ready';

export const NAVIGATION_ERROR =
    'navigation:error';

export const NAVIGATION_ABORT =
    'navigation:abort';

// =========================================
// INTERNAL
// =========================================

function createNavigationEvent(
    type,
    detail,
)
{
    return new CustomEvent(
        type,
        {
            detail,
        },
    );
}

// =========================================
// EMIT
// =========================================

export function emitNavigationEvent(
    type,
    detail = {},
)
{
    debug(
        'NAVIGATION',
        type,
        detail,
    );

    document.dispatchEvent(
        createNavigationEvent(
            type,
            detail,
        ),
    );
}