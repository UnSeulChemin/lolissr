// =========================================
// DEBUG
// =========================================

import {
    config,
} from './config.js';

import {
    logInfo,
    logWarn,
    logError,
} from './logger.js';

// =========================================
// HELPERS
// =========================================

function canDebug()
{
    return config.debug;
}

// =========================================
// DEBUG
// =========================================

export function debug(
    scope,
    ...args
)
{
    if (
        !canDebug()
    ) {

        return;
    }

    logInfo(
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
    if (
        !canDebug()
    ) {

        return;
    }

    logWarn(
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
    /*
    |--------------------------------------------------------------------------
    | ERRORS ALWAYS LOGGED
    |--------------------------------------------------------------------------
    */

    logError(
        scope,
        error,
        ...args,
    );
}