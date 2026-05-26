// ==================================================
// Debug
// ==================================================

import {
    state,
} from './state.js';

export function debug(
    scope,
    ...args
)
{
    if (
        !state.debug
    ) {
        return;
    }

    console.log(
        `%c[${scope}]`,
        'color:#9b5cff;font-weight:bold',
        ...args,
    );
}

export function debugError(
    scope,
    error,
)
{
    console.error(
        `%c[${scope}]`,
        'color:#ff4d6d;font-weight:bold',
        error,
    );
}