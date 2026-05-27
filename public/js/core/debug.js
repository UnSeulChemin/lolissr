// =========================================
// DEBUG
// =========================================

import {
    config,
} from './config.js';

// =========================================
// STYLES
// =========================================

const styles =
    Object.freeze({

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
    });

// =========================================
// HELPERS
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

    const logger =
        console[type]
        || console.log;

    logger(
        `%c[${scope}]`,
        styles[type]
        || styles.log,
        ...args,
    );
}

// =========================================
// DEBUG
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
// WARN
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
// ERROR
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