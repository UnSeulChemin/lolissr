// =========================================
// NAVIGATION PROTOCOL
// =========================================

import {
    debug,
} from '../core/debug.js';

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
        new CustomEvent(
            type,
            {
                detail,
            },
        ),
    );
}