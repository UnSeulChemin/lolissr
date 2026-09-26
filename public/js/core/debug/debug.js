// =========================================
// DEBUG
// =========================================

import {
    logInfo,
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
