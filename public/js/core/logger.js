// =========================================
// LOGGER
// =========================================

import {
    config,
} from './config.js';

// =========================================
// CONFIG
// =========================================

const LOG_HISTORY_LIMIT =
    500;

// =========================================
// STYLES
// =========================================

const styles =
    Object.freeze({

        info:
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
// STATE
// =========================================

const logs =
    [];

// =========================================
// HELPERS
// =========================================

function canDebug()
{
    return config.debug;
}

function createEntry(
    level,
    scope,
    messages,
)
{
    return {
        timestamp:
            Date.now(),

        level,

        scope,

        messages,
    };
}

function pushLog(
    entry,
)
{
    logs.push(
        entry,
    );

    if (
        logs.length
        > LOG_HISTORY_LIMIT
    ) {

        logs.shift();
    }
}

function print(
    level,
    scope,
    ...messages
)
{
    /*
    |--------------------------------------------------------------------------
    | ONLY INFO/WARN NEED DEBUG MODE
    |--------------------------------------------------------------------------
    */

    if (
        level !== 'error'
        && !canDebug()
    ) {

        return;
    }

    const logger =
        console[level]
        || console.log;

    logger(
        `%c[${scope}]`,
        styles[level]
        || styles.info,
        ...messages,
    );
}

function write(
    level,
    scope,
    ...messages
)
{
    const entry =
        createEntry(
            level,
            scope,
            messages,
        );

    pushLog(
        entry,
    );

    print(
        level,
        scope,
        ...messages,
    );
}

// =========================================
// INFO
// =========================================

export function logInfo(
    scope,
    ...messages
)
{
    write(
        'info',
        scope,
        ...messages,
    );
}

// =========================================
// WARN
// =========================================

export function logWarn(
    scope,
    ...messages
)
{
    write(
        'warn',
        scope,
        ...messages,
    );
}

// =========================================
// ERROR
// =========================================

export function logError(
    scope,
    ...messages
)
{
    write(
        'error',
        scope,
        ...messages,
    );
}

// =========================================
// HISTORY
// =========================================

export function getLogs()
{
    return [
        ...logs,
    ];
}

// =========================================
// CLEAR
// =========================================

export function clearLogs()
{
    logs.length =
        0;
}

// =========================================
// GLOBAL DEBUG
// =========================================

window.__LOGS__ =
    getLogs;