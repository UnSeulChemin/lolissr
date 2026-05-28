// =========================================
// ROUTER CLEANUP
// =========================================

import {
    debugError,
} from '../core/debug/debug.js';

// =========================================
// STATE
// =========================================

const cleanupCallbacks =
    new Set();

// =========================================
// REGISTER
// =========================================

export function registerCleanup(
    callback,
)
{
    if (
        typeof callback
        !== 'function'
    ) {
        return () => {};
    }

    cleanupCallbacks.add(
        callback,
    );

    return () =>
    {
        cleanupCallbacks.delete(
            callback,
        );
    };
}

// =========================================
// RUN
// =========================================

export function runCleanup()
{
    for (
        const callback
        of cleanupCallbacks
    )
    {
        try {

            callback();

        } catch (error) {

            debugError(
                'ROUTER-CLEANUP',
                error,
            );
        }
    }

    cleanupCallbacks.clear();
}