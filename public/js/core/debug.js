// =========================================
// DEBUG
// =========================================

import {
    logInfo,
    logWarn,
    logError,
} from './logger.js';

// =========================================
// DEBUG
// =========================================

export function debug(
    scope,
    ...args
)
{
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
    logError(
        scope,
        error,
        ...args,
    );
}