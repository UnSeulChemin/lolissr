// ==================================================
// Debug
// ==================================================

import {
    state,
} from './state.js';

// ==================================================
// Debug
// ==================================================

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
        `
        color:#9b5cff;
        font-weight:bold;
        `,
        ...args,
    );
}

// ==================================================
// Debug Error
// ==================================================

export function debugError(
    scope,
    error,
    ...args
)
{
    if (
        !state.debug
    ) {
        return;
    }

    console.error(
        `%c[${scope}]`,
        `
        color:#ff4d6d;
        font-weight:bold;
        `,
        error,
        ...args,
    );
}

// ==================================================
// Debug Warn
// ==================================================

export function debugWarn(
    scope,
    ...args
)
{
    if (
        !state.debug
    ) {
        return;
    }

    console.warn(
        `%c[${scope}]`,
        `
        color:#ffb84d;
        font-weight:bold;
        `,
        ...args,
    );
}