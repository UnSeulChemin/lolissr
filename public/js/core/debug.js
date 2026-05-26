// =========================================
// DEBUG
// =========================================

import {
    config,
} from './config.js';

// =========================================
// Styles
// =========================================

const styles =
{
    log:
    `
    color:#9b5cff;
    font-weight:bold;
    `,

    warn:
    `
    color:#ffb84d;
    font-weight:bold;
    `,

    error:
    `
    color:#ff4d6d;
    font-weight:bold;
    `,
};

// =========================================
// Helpers
// =========================================

function canDebug()
{
    return config.debug;
}

function print(
    type,
    scope,
    ...args
)
{
    if (
        !canDebug()
    ) {
        return;
    }

    console[type](
        `%c[${scope}]`,
        styles[type],
        ...args,
    );
}

// =========================================
// Debug
// =========================================

export function debug(
    scope,
    ...args
)
{
    print(
        'log',
        scope,
        ...args,
    );
}

// =========================================
// Debug Warn
// =========================================

export function debugWarn(
    scope,
    ...args
)
{
    print(
        'warn',
        scope,
        ...args,
    );
}

// =========================================
// Debug Error
// =========================================

export function debugError(
    scope,
    error,
    ...args
)
{
    print(
        'error',
        scope,
        error,
        ...args,
    );
}