// =================================================
// DÉBOGAGE
// =================================================

import {
    logInfo,
    logError,
} from './logger.js';

// =================================================
// DÉBOGAGE
// =================================================

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

// =================================================
// ERREUR
// =================================================

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
